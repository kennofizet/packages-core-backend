<?php

namespace Kennofizet\PackagesCore\Models\Zone;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait ZoneRelations
{
    /**
     * Zone belongs to many users (many-to-many through zone_users pivot)
     */
    public function users()
    {
        $zoneUsersTableName = (new \Kennofizet\PackagesCore\Models\ZoneUser())->getTable();

        return $this->belongsToMany(
            \Kennofizet\PackagesCore\Models\User::class,
            $zoneUsersTableName,
            'zone_id',
            'user_id'
        )->using(\Kennofizet\PackagesCore\Models\ZoneUser::class)->withTimestamps();
    }
}
