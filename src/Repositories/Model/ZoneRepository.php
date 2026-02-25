<?php

namespace Kennofizet\PackagesCore\Repositories\Model;

use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;

class ZoneRepository
{
    public function create(array $data): Zone
    {
        return Zone::create([
            'name' => $data['name'],
            'server_id' => $data['server_id'] ?? BaseModelActions::currentServerId(),
        ]);
    }

    public function update(Zone $zone, array $data): Zone
    {
        $zone->update([
            'name' => $data['name'],
            'server_id' => $data['server_id'] ?? $zone->server_id,
        ]);
        return $zone;
    }

    public function delete(Zone $zone): bool
    {
        return (bool) $zone->delete();
    }
}
