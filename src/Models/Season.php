<?php

namespace Kennofizet\PackagesCore\Models;

use Kennofizet\PackagesCore\Core\Model\BaseModel;

class Season extends BaseModel
{
    protected $fillable = [
        'zone_id',
        'name',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getTable()
    {
        return self::getPivotTableName(config('packages-core.season_table', 'knf_core_seasons'));
    }
}
