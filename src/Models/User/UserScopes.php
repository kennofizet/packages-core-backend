<?php

namespace Kennofizet\PackagesCore\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;

trait UserScopes
{
    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('id', $search);
    }

    public function scopeByServer(Builder $query)
    {
        if (empty(config('packages-core.user_server_id_column'))) {
            return $query;
        }
        return $query->where(function ($q) {
            $q->where(config('packages-core.user_server_id_column'), BaseModelActions::currentServerId());
        });
    }

    public function scopeById(Builder $query, $id)
    {
        return $query->where('id', $id);
    }
}
