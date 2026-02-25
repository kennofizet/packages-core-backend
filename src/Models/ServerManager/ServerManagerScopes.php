<?php

namespace Kennofizet\PackagesCore\Models\ServerManager;

use Illuminate\Database\Eloquent\Builder;

trait ServerManagerScopes
{
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByServer(Builder $query, $serverId)
    {
        if ($serverId === null) {
            return $query->whereNull('server_id');
        }
        return $query->where('server_id', $serverId);
    }
}
