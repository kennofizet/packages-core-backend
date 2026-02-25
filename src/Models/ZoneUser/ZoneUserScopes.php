<?php

namespace Kennofizet\PackagesCore\Models\ZoneUser;

use Illuminate\Database\Eloquent\Builder;

trait ZoneUserScopes
{
    public function scopeByUserId(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByZoneId(Builder $query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }
}
