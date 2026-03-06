<?php

namespace Kennofizet\PackagesCore\Core\Model;

trait BaseModelActions
{
    public static function arrayRemoveEmpty($data)
    {
        if ($data) {
            return array_values(array_filter($data, function ($value) {
                return ($value !== "" and $value !== null);
            }));
        }
        return [];
    }

    /**
     * Get array of zone IDs that the current user is in
     */
    public static function currentUserZoneIds()
    {
        $zoneIds = request()->attributes->get('knf_core_user_zone_ids', []);
        if (!is_array($zoneIds)) {
            return [];
        }
        return array_filter($zoneIds, function ($id) {
            return !empty($id);
        });
    }

    public static function currentUserZoneId()
    {
        $zoneId = request()->attributes->get('knf_core_user_zone_id_current');
        if (empty($zoneId)) {
            return null;
        }
        return $zoneId;
    }

    /**
     * Get array of zone IDs that the current user manages
     */
    public static function currentUserManagedZoneIds()
    {
        $zoneIds = request()->attributes->get('knf_core_user_managed_zone_ids', []);
        if (!is_array($zoneIds)) {
            return [];
        }
        return array_filter($zoneIds, function ($id) {
            return !empty($id);
        });
    }

    /**
     * Get current server ID from request attributes
     */
    public static function currentServerId()
    {
        $serverIdColumn = config('packages-core.user_server_id_column');
        if (empty($serverIdColumn)) {
            return null;
        }

        $serverId = request()->attributes->get('knf_core_user_server_id');
        if (empty($serverId)) {
            return null;
        }
        return $serverId;
    }

    /**
     * Get the server ID that the current user manages
     */
    public static function currentUserManagedServerId(): ?int
    {
        return request()->attributes->get('knf_core_user_managed_server_id');
    }

    /**
     * Check if current user can manage a specific server
     */
    public static function canManageServer(?int $serverId): bool
    {
        $managedServerId = self::currentUserManagedServerId();
        if ($serverId === null && $managedServerId === null) {
            return true;
        }
        return $managedServerId !== null && $managedServerId === $serverId;
    }

    /**
     * Check if current user can manage a specific zone
     */
    public static function canManageZone(int $zoneId): bool
    {
        $managedZoneIds = self::currentUserManagedZoneIds();
        return in_array($zoneId, $managedZoneIds);
    }

    /**
     * Get pivot table name with configured prefix.
     */
    protected static function getPivotTableName(string $tableName): string
    {
        $tablePrefix = config('packages-core.table_prefix', '');
        return $tablePrefix . $tableName;
    }
}
