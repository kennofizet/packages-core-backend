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
     * Relies on attributes set by ValidateRewardPlayToken middleware.
     */
    public function checkUser(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('rewardplay_user_id');

        if (empty($userId)) {
            return $this->apiErrorResponse('User not authenticated', 401);
        }

        $managedServerId = $request->attributes->get('rewardplay_user_managed_server_id');
        $managedZoneIds = $request->attributes->get('rewardplay_user_managed_zone_ids', []);

        $isManager = !empty($managedServerId)
            || (!empty($managedZoneIds) && is_array($managedZoneIds) && count($managedZoneIds) > 0);

        return $this->apiResponseWithContext('Success', [
            'is_manager' => $isManager,
        ]);
    }
}
