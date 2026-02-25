<?php

namespace Kennofizet\PackagesCore\Core\Model;

use Illuminate\Database\Eloquent\Builder;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;
use Kennofizet\PackagesCore\Helpers\Constant as CoreConstant;

trait BaseModelScopes
{
    public function scopeReturnNull(Builder $query)
    {
        return $query->whereRaw('1 != 1');
    }

    public function scopeIsInZone(Builder $query)
    {
        $zoneId = BaseModelActions::currentUserZoneId();
        if (empty($zoneId)) {
            return $query->returnNull();
        }

        $table = $query->getModel()->getTable();
        return $query->where(function ($q) use ($table, $zoneId) {
            $q->where($table . '.' . CoreConstant::ZONE_ID_COLUMN, $zoneId);
        });
    }

    public function scopeWithoutDeleteStatus(Builder $query)
    {
        $table = $query->getModel()->getTable();
        return $query->where($table . '.' . CoreConstant::IS_DELETED_STATUS_COLUMN, 0);
    }

    public function scopeIsActive(Builder $query, $column = CoreConstant::STATUS_COLUMN)
    {
        $table = $query->getModel()->getTable();
        if (strpos($column, '.') === false) {
            $column = $table . '.' . $column;
        }
        return $query->where($column, CoreConstant::STATUS_ON);
    }
}
