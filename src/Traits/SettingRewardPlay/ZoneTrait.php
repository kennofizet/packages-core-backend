<?php

namespace Kennofizet\PackagesCore\Traits\SettingRewardPlay;

use Kennofizet\PackagesCore\Services\ZoneService;

trait ZoneTrait
{
    public function getZones($filters = [])
    {
        return app(ZoneService::class)->getZones($filters);
    }

    public function createZone(array $data)
    {
        return app(ZoneService::class)->createZone($data);
    }

    public function editZone(int $zoneId, array $data)
    {
        return app(ZoneService::class)->editZone($zoneId, $data);
    }

    public function deleteZone(int $zoneId)
    {
        return app(ZoneService::class)->deleteZone($zoneId);
    }
}
