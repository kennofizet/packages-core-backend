<?php

namespace Kennofizet\PackagesCore\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\ZoneUser;

trait UserRelations
{
    /**
     * User belongs to many zones (many-to-many through zone_users pivot)
     */
    public function zones(): BelongsToMany
    {
        $zoneUsersTableName = (new ZoneUser())->getTable();
        return $this->belongsToMany(
            Zone::class,
            $zoneUsersTableName,
            'user_id',
            'zone_id'
        )->using(ZoneUser::class)->withTimestamps();
    }

    /**
     * User has one profile (rewardplay). Returns null if UserProfile doesn't exist.
     */
    public function profile(): HasOne
    {
        $profileClass = 'Kennofizet\RewardPlay\Models\UserProfile';
        if (class_exists($profileClass)) {
            return $this->hasOne($profileClass, 'user_id');
        }
        // Fallback: return empty relation
        return $this->hasOne(static::class, 'id')->whereRaw('1=0');
    }

    /**
     * Get server managers for this user.
     */
    public function serverManagers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Kennofizet\PackagesCore\Models\ServerManager::class, 'user_id');
    }
}

