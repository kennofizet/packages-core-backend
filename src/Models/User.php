<?php

namespace Kennofizet\PackagesCore\Models;

use Kennofizet\PackagesCore\Models\User\UserRelations;
use Kennofizet\PackagesCore\Models\User\UserScopes;
use Kennofizet\PackagesCore\Models\User\UserActions;
use Kennofizet\PackagesCore\Helpers\Constant as CoreConstant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use UserRelations, UserActions, UserScopes;

    protected $hidden = [
        CoreConstant::IS_DELETED_STATUS_COLUMN,
        CoreConstant::ZONE_ID_COLUMN,
    ];

    public function getTable()
    {
        return config('packages-core.table_user', 'users');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('by_server_user', function (Builder $builder) {
            if (empty(config('packages-core.user_server_id_column'))) {
                return;
            }
            $builder->byServer();
        });
    }
}
