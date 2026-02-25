<?php

namespace Kennofizet\PackagesCore\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Core\Model\BaseModelManage;
use Kennofizet\PackagesCore\Core\Model\BaseModelRelations;
use Kennofizet\PackagesCore\Core\Model\BaseModelScopes;
use Kennofizet\PackagesCore\Helpers\Constant as CoreConstant;

class BaseModel extends Model
{
    use BaseModelActions;
    use BaseModelManage;
    use BaseModelRelations;
    use BaseModelScopes;
    use SoftDeletes;

    protected $hidden = [
        CoreConstant::IS_DELETED_STATUS_COLUMN,
        CoreConstant::ZONE_ID_COLUMN,
    ];

    protected static function boot()
    {
        parent::boot();

        // Apply global scope for zone filtering
        static::addGlobalScope('is_in_zone', function (Builder $builder) {
            $table = $builder->getModel()->getTable();
            $array_skips = [];

            if (self::tableHasColumn($table, CoreConstant::ZONE_ID_COLUMN) && !in_array($table, $array_skips)) {
                $builder->isInZone();
            }
        });

        // Global delete_status scope
        static::addGlobalScope('without_delete_status', function (Builder $builder) {
            try {
                $table = $builder->getModel()->getTable();

                $userTable = (new \Kennofizet\PackagesCore\Models\User())->getTable();
                $array_skips = [$userTable];

                if (self::tableHasColumn($table, CoreConstant::IS_DELETED_STATUS_COLUMN) && !in_array($table, $array_skips)) {
                    $builder->withoutDeleteStatus();
                }
            } catch (\Exception $e) {
            }
        });

        // Auto-add zone_id when creating models
        static::creating(function ($model) {
            $table = $model->getTable();

            if (self::tableHasColumn($table, CoreConstant::ZONE_ID_COLUMN)) {
                if (empty($model->zone_id) && request()) {
                    $currentZoneId = request()->attributes->get('rewardplay_user_zone_id_current');
                    if (!empty($currentZoneId)) {
                        $model->zone_id = $currentZoneId;
                    }
                }
            }
        });
    }
}
