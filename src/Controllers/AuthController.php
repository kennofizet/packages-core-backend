<?php

namespace Kennofizet\PackagesCore\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;

class AuthController
{
    use GlobalDataTrait;

    /**
     * Check user authentication and manager status.
     * Relies on attributes set by middleware.
     */
    public function checkUser(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('knf_core_user_id');

        if (empty($userId)) {
            return $this->apiErrorResponse('User not authenticated', 401);
        }

        $managedServerId = $request->attributes->get('knf_core_user_managed_server_id');
        $managedZoneIds = $request->attributes->get('knf_core_user_managed_zone_ids', []);

        $configPaths = [
            'core' => config('packages-core.api_prefix', 'api/knf'),
            'workpoint' => config('packages-core.api_prefix', 'api/knf') . (config('workpoint.api_prefix', '') ?? '/' . config('workpoint.api_prefix', '')),
            'rewardplay' => config('packages-core.api_prefix', 'api/knf') . (config('rewardplay.api_prefix', '') ?? '/' . config('rewardplay.api_prefix', '')),
            'feedback' => config('packages-core.api_prefix', 'api/knf') . (config('feedback.api_prefix', '') ?? '/' . config('feedback.api_prefix', '')),
        ];

        $isManager = !empty($managedServerId)
            || (!empty($managedZoneIds) && is_array($managedZoneIds) && count($managedZoneIds) > 0);

        return $this->apiResponseWithContext('Success', [
            'is_manager' => $isManager,
            'config_paths' => $configPaths,
        ]);
    }
}
