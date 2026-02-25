<?php

namespace Kennofizet\PackagesCore\Core\Model;

trait BaseModelManage
{
    /**
     * Check if table has column
     */
    public static function tableHasColumn(string $table, string $column): bool
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        return in_array($column, $columns);
    }
}
