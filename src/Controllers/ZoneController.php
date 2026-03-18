<?php

namespace Kennofizet\PackagesCore\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Services\ZoneService;

class ZoneController
{
    use GlobalDataTrait, BaseModelActions;

    protected ZoneService $zoneService;

    public function __construct(ZoneService $zoneService)
    {
        $this->zoneService = $zoneService;
    }

    /**
     * Return zones the current user belongs to (player endpoint).
     */
    public function index(Request $request): JsonResponse
    {
        $zoneIds = self::currentUserZoneIds();
        $timezone = config('app.timezone', 'UTC');

        if (empty($zoneIds)) {
            return $this->apiResponseWithContext('Success', [
                'zones' => [],
                'timezone' => $timezone,
                'is_manager' => self::isManager(),
            ]);
        }

        $zones = Zone::byZoneIds($zoneIds)->get()->map(fn($z) => [
            'id' => $z->id,
            'name' => $z->name,
        ])->toArray();

        return $this->apiResponseWithContext('Success', [
            'zones' => $zones,
            'timezone' => $timezone,
            'is_manager' => self::isManager(),
        ]);
    }

    /**
     * Get zones the current user can manage (player endpoint).
     */
    public function managed(Request $request): JsonResponse
    {
        $zones = $this->zoneService->getZonesUserCanManage();

        return $this->apiResponseWithContext('Success', [
            'zones' => $zones,
        ]);
    }

    /**
     * List all zones (with optional filters) — settings endpoint.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'perPage', 'currentPage']);
        $zones = $this->zoneService->getZones($filters);

        return $this->apiResponseWithContext('Success', [
            'zones' => $zones,
        ]);
    }

    /**
     * Create zone.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->only(['name', 'server_id']);

        try {
            $zone = $this->zoneService->createZone($data);

            return $this->apiResponseWithContext('Success', ['zone' => $zone], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update zone.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->only(['name', 'server_id']);

        try {
            $zone = $this->zoneService->editZone($id, $data);

            if (!$zone) {
                return $this->apiErrorResponse('Zone not found', 404);
            }

            return $this->apiResponseWithContext('Success', ['zone' => $zone]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete zone.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $deleted = $this->zoneService->deleteZone($id);

            if (!$deleted) {
                return $this->apiErrorResponse('Zone not found', 404);
            }

            return $this->apiResponseWithContext('Success', ['message' => 'Zone deleted']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * List server users + zone assigned user ids.
     */
    public function users(Request $request, int $id): JsonResponse
    {
        try {
            $filters = $request->only(['search']);
            $serverUsers = $this->zoneService->getServerUsers($filters);
            $zoneUsers = $this->zoneService->getZoneUsers($id);

            $assignedIds = array_map(fn($u) => $u['id'] ?? ($u->id ?? null), $zoneUsers);

            return $this->apiResponseWithContext('Success', [
                'users' => $serverUsers,
                'assigned_user_ids' => $assignedIds,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Assign a user to a zone.
     */
    public function assignUser(Request $request, int $id): JsonResponse
    {
        $userId = $request->input('user_id');

        try {
            $this->zoneService->assignUserToZone($id, (int) $userId);

            return $this->apiResponseWithContext('Success', ['message' => 'User assigned']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove a user from a zone.
     */
    public function removeUser(Request $request, int $id, int $userId): JsonResponse
    {
        try {
            $this->zoneService->removeUserFromZone($id, $userId);

            return $this->apiResponseWithContext('Success', ['message' => 'User removed']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle exception — validation or generic.
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->apiErrorResponse($e->getMessage(), 422);
        }

        return $this->apiErrorResponse($e->getMessage(), 403);
    }
}
