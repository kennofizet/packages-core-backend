<?php

namespace Kennofizet\PackagesCore\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kennofizet\PackagesCore\Services\TokenService;
use Kennofizet\PackagesCore\Models\ZoneUser;
use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;
use Illuminate\Support\Facades\DB;

class ValidateCoreToken
{
    use GlobalDataTrait;

    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Knf-Token');

        if (!$token) {
            return $this->apiErrorResponse('Knf token is required', 401);
        }

        $userId = $this->tokenService->validateToken($token);

        if (!$userId) {
            return $this->apiErrorResponse('Invalid or inactive token', 401);
        }

        $serverColumn = config('packages-core.user_server_id_column');
        $user = $this->resolveUserWithServer($userId, $serverColumn);

        if (empty($user)) {
            return $this->apiErrorResponse('User not found', 404);
        }

        $serverId = null;
        if (!empty($serverColumn) && !empty($user->{$serverColumn})) {
            $serverId = $user->{$serverColumn};
            $request->attributes->set('knf_core_user_server_id', $serverId);
        }

        $managedZoneIds = $this->getUserManagedZoneIds($userId);
        $request->attributes->set('knf_core_user_managed_zone_ids', $managedZoneIds);

        $zoneIds = $this->getUserZoneIds($userId);
        $request->attributes->set('knf_core_user_zone_ids', $zoneIds);

        $request->attributes->set('knf_core_user_id', $userId);

        $managedServerId = $this->getUserManagedServerId($userId);
        $request->attributes->set('knf_core_user_managed_server_id', $managedServerId);

        if (empty($managedZoneIds) && empty($zoneIds) && empty($managedServerId)) {
            return $this->apiErrorResponse('User not in any zone or not managing any server', 403);
        }

        $currentZoneId = $this->getAndValidateZoneId($request, $zoneIds, $managedZoneIds);
        if ($currentZoneId === false) {
            return $this->apiErrorResponse('Invalid or unauthorized zone_id', 403);
        }

        $request->attributes->set('knf_core_user_zone_id_current', $currentZoneId);

        $allZoneIds = array_unique(array_merge($zoneIds, $managedZoneIds));
        $validationError = $this->validateRequestPermissions($request, $managedServerId, $allZoneIds);
        if ($validationError) {
            return $validationError;
        }

        return $next($request);
    }

    protected function getAndValidateZoneId(Request $request, array $zoneIds, array $managedZoneIds): ?int
    {
        $allZoneIds = array_unique(array_merge($zoneIds, $managedZoneIds));
        $requestZoneId = $request->header('X-Knf-Zone-Id');

        if (empty($requestZoneId)) {
            return !empty($allZoneIds) ? (int) $allZoneIds[0] : null;
        }

        $requestZoneId = (int) $requestZoneId;

        if (!in_array($requestZoneId, $allZoneIds)) {
            return false;
        }

        return $requestZoneId;
    }

    protected function validateRequestPermissions(Request $request, ?int $managedServerId, array $allZoneIds)
    {
        if ($request->has('server_id')) {
            $requestServerId = $request->input('server_id');
            if (!empty($requestServerId) && $requestServerId != $managedServerId) {
                return $this->apiErrorResponse('You do not have permission to manage this server', 403);
            }
        }

        if ($request->has('zone_id')) {
            $requestZoneId = $request->input('zone_id');
            if (!empty($requestZoneId) && !in_array($requestZoneId, $allZoneIds)) {
                return $this->apiErrorResponse('You do not have permission to access this zone', 403);
            }
        }

        $routeZoneId = $request->route('zone') ?? $request->route('zoneId');
        if (!empty($routeZoneId) && !in_array($routeZoneId, $allZoneIds)) {
            return $this->apiErrorResponse('You do not have permission to access this zone', 403);
        }

        $routeServerId = $request->route('server') ?? $request->route('serverId');
        if (!empty($routeServerId) && $routeServerId != $managedServerId) {
            return $this->apiErrorResponse('You do not have permission to manage this server', 403);
        }

        return null;
    }

    protected function resolveUserWithServer(int $userId, ?string $serverColumn = null)
    {
        $user = \Kennofizet\PackagesCore\Models\User::byId($userId)->first();

        if (empty($user)) {
            return null;
        }

        if (empty($serverColumn)) {
            return $user;
        }

        if (empty($user->{$serverColumn}) || $user->{$serverColumn} === null) {
            return false;
        }

        return $user;
    }

    protected function getUserManagedZoneIds(int $userId): array
    {
        $serverIds = ServerManager::byUser($userId)->pluck('server_id')->toArray();

        if (empty($serverIds)) {
            return [];
        }

        $zoneIds = Zone::byServerIds($serverIds)->pluck('id')->toArray();

        return array_values(array_filter($zoneIds, function ($id) {
            return !empty($id);
        }));
    }

    protected function getUserZoneIds(int $userId): array
    {
        $zoneIdsFromPivot = ZoneUser::byUserId($userId)->pluck('zone_id')->toArray();
        $zoneIdsFromManager = $this->getUserManagedZoneIds($userId);
        $zoneIds = array_unique(array_merge($zoneIdsFromPivot, $zoneIdsFromManager));
        $zoneIds = Zone::byZoneIds($zoneIds)->pluck('id')->toArray();

        return array_values(array_filter($zoneIds, function ($id) {
            return !empty($id);
        }));
    }

    protected function getUserManagedServerId(int $userId): ?int
    {
        $serverColumn = config('packages-core.user_server_id_column');

        if (empty($serverColumn)) {
            $managerNull = ServerManager::withoutGlobalScopes()->byUser($userId)->byServer(null)->first();
            return $managerNull ? null : null;
        }

        $user = $this->resolveUserWithServer($userId, $serverColumn);
        if (empty($user) || $user === false) {
            return null;
        }

        $serverId = $user->{$serverColumn} ?? null;
        if ($serverId === null || $serverId === '') {
            $managerNull = ServerManager::withoutGlobalScopes()->byUser($userId)->byServer(null)->first();
            return $managerNull ? null : null;
        }

        $checkManagedServer = ServerManager::byUser($userId)->byServer($serverId)->first();
        if (empty($checkManagedServer)) {
            return null;
        }

        return (int) $serverId;
    }
}
