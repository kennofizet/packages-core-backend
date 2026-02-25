<?php

namespace Kennofizet\PackagesCore\Models\Zone;

use Illuminate\Database\Eloquent\Builder;
use Kennofizet\PackagesCore\Helpers\Constant as CoreConstant;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;

trait ZoneScopes
{
    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    public function scopeByServer(Builder $query)
    {
        $serverId = BaseModelActions::currentServerId();
        if (empty($serverId)) {
            return $query;
        }

        $table = $query->getModel()->getTable();
        return $query->where($table . '.' . CoreConstant::SERVER_ID_COLUMN, $serverId);
    }

    public function scopeByServerId(Builder $query, $serverId)
    {
        $table = $query->getModel()->getTable();
        $col = $table . '.' . CoreConstant::SERVER_ID_COLUMN;
        if ($serverId === null) {
            return $query->whereNull($col);
        }
        return $query->where($col, $serverId);
    }

    public function scopeByServerIds(Builder $query, array $serverIds)
    {
        if (empty($serverIds)) {
            return $query->returnNull();
        }

        $table = $query->getModel()->getTable();
        $col = $table . '.' . CoreConstant::SERVER_ID_COLUMN;
        $nonNull = array_values(array_filter($serverIds, fn($id) => $id !== null));
        $hasNull = in_array(null, $serverIds, true);

        if (!empty($nonNull) && $hasNull) {
            return $query->where(function (Builder $q) use ($col, $nonNull) {
                $q->whereIn($col, $nonNull)->orWhereNull($col);
            });
        }
        if ($hasNull) {
            return $query->whereNull($col);
        }
        return $query->whereIn($col, $nonNull);
    }

    public function scopeByZoneIds(Builder $query, array $zoneIds)
    {
        if (empty($zoneIds)) {
            return $query->returnNull();
        }
        return $query->whereIn('id', $zoneIds);
    }
}
