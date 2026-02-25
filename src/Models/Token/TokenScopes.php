<?php

namespace Kennofizet\PackagesCore\Models\Token;

use Illuminate\Database\Eloquent\Builder;

trait TokenScopes
{
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByToken(Builder $query, $token)
    {
        return $query->where('token', $token);
    }
}
