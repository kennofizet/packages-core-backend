<?php

namespace Kennofizet\PackagesCore\Models\Zone;

use Kennofizet\PackagesCore\Core\Model\BaseModelResponse;
use Kennofizet\PackagesCore\Models\Zone\ZoneConstant;
use Kennofizet\PackagesCore\Helpers\Constant as HelperConstant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ZoneModelResponse extends BaseModelResponse
{
    public static function getAvailableModeDefault(): string
    {
        return ZoneConstant::API_ZONE_LIST_PAGE;
    }

    public static function formatZone($zone, $mode = ''): array
    {
        if (!$zone) {
            return [];
        }

        if (in_array($mode, [self::getAvailableModeDefault()])) {
            return [
                'id' => $zone->id,
                'name' => $zone->name,
            ];
        } elseif (in_array($mode, [HelperConstant::REPONSE_MODE_SELECTER_API])) {
            return [
                'id' => $zone->id,
                'name' => $zone->name,
            ];
        }

        return [
            'id' => $zone->id,
            'name' => $zone->name,
        ];
    }

    public static function formatZones($zones, ?string $mode = null): array
    {
        $mode = $mode ?? self::getAvailableModeDefault();

        if ($zones instanceof LengthAwarePaginator) {
            return [
                'data' => $zones->map(fn($z) => self::formatZone($z, $mode)),
                'current_page' => $zones->currentPage(),
                'total' => $zones->total(),
                'last_page' => $zones->lastPage(),
            ];
        }

        if ($zones instanceof Collection) {
            return $zones->map(fn($z) => self::formatZone($z, $mode))->toArray();
        }

        return [];
    }
}
