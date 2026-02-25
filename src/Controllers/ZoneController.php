<?php

namespace Kennofizet\PackagesCore\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kennofizet\PackagesCore\Traits\GlobalDataTrait;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Models\Zone;

class ZoneController
{
    use GlobalDataTrait, BaseModelActions;

    /**
     * Return zones the current user belongs to.
     * Zone IDs are set by ValidateRewardPlayToken middleware.
     */
    public function index(Request $request): JsonResponse
    {
        $zoneIds = self::currentUserZoneIds();
        $timezone = config('app.timezone', 'UTC');

        if (empty($zoneIds)) {
            return $this->apiResponseWithContext('Success', [
                'zones' => [],
                'timezone' => $timezone,
            ]);
        }

        $zones = Zone::byZoneIds($zoneIds)->get()->map(fn($z) => [
            'id' => $z->id,
            'name' => $z->name,
        ])->toArray();

        return $this->apiResponseWithContext('Success', [
            'zones' => $zones,
            'timezone' => $timezone,
        ]);
    }
}
