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

    /**
     * By default, expose only the minimum fields needed by API consumers.
     * (id + configured display-name column)
     *
     * Note: We keep CoreConstant columns hidden too, even if the host app adds them to visible.
     */
    protected $hidden = [
        '*',
        CoreConstant::IS_DELETED_STATUS_COLUMN,
        CoreConstant::ZONE_ID_COLUMN,
    ];

    /**
     * Make sure id + user_col_name remain visible even with $hidden = ['*'].
     *
     * @return array<int, string>
     */
    public function getVisible(): array
    {
        $nameCol = $this->getNameColumn();
        $visible = ['id'];
        if (is_string($nameCol) && $nameCol !== '') {
            $visible[] = $nameCol;
        }
        return $visible;
    }

    public function getTable()
    {
        return config('packages-core.table_user', 'users');
    }

    public function getNameColumn()
    {
        return config('packages-core.user_col_name', 'name');
    }

    public function getNameAttribute()
    {
        if (empty($this->getNameColumn())) {
            return 'Unknown';
        }
        
        return $this->attributes[$this->getNameColumn()] ?? 'Unknown';
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
