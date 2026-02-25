<?php

namespace Kennofizet\PackagesCore\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;

class EnsureUserIsManager
{
    use GlobalDataTrait;

    public function handle(Request $request, Closure $next)
    {
        $managedServerId = $request->attributes->get('rewardplay_user_managed_server_id');
        $managedZoneIds = $request->attributes->get('rewardplay_user_managed_zone_ids', []);

        if (empty($managedServerId) && (empty($managedZoneIds) || count($managedZoneIds) === 0)) {
            return $this->apiErrorResponse('You do not have manager permission to access this resource', 403);
        }

        return $next($request);
    }
}
