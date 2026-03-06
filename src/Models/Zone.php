<?php

namespace Kennofizet\PackagesCore\Models;

use Kennofizet\PackagesCore\Core\Model\BaseModel;
use Kennofizet\PackagesCore\Models\Zone\ZoneActions;
use Kennofizet\PackagesCore\Models\Zone\ZoneRelations;
use Kennofizet\PackagesCore\Models\Zone\ZoneScopes;

class Zone extends BaseModel
{
    use ZoneActions, ZoneRelations, ZoneScopes;

    public function getTable()
    {
        return self::getPivotTableName('knf_core_zones');
    }

    protected $fillable = [
        'name',
        'server_id',
    ];

    protected static function boot()
    {
        parent::boot();

        // Apply server filter globally
        static::addGlobalScope('by_server', function ($builder) {
            $builder->byServer();
        });
    }
}
