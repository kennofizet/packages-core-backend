<?php

namespace Kennofizet\PackagesCore\Models\Zone;

use Kennofizet\PackagesCore\Helpers\Constant as HelperConstant;
use Kennofizet\PackagesCore\Models\Zone\ZoneConstant;

class ZoneRelationshipSetting
{
    protected static $settings = [
        ZoneConstant::API_ZONE_LIST_PAGE => [],
        HelperConstant::REPONSE_MODE_SELECTER_API => [],
    ];

    protected static $countSettings = [];

    public static function getRelationships(?string $mode = null): array
    {
        return self::$settings[$mode] ?? [];
    }

    public static function getCountSettings(?string $mode = null): array
    {
        return self::$countSettings[$mode] ?? [];
    }

    public static function buildWithCountArray(?string $mode = null, $zone = null): array
    {
        $mode = $mode ?? ZoneModelResponse::getAvailableModeDefault();
        $configs = self::getCountSettings($mode);
        $withCountArray = [];

        foreach ($configs as $alias => $config) {
            if (is_string($alias)) {
                $withCountArray[$alias] = str_replace(' as ' . $config, '', $alias);
            } else {
                $withCountArray[] = $config;
            }
        }

        return $withCountArray;
    }

    public static function buildWithArray(?string $mode = null): array
    {
        $mode = $mode ?? ZoneModelResponse::getAvailableModeDefault();
        $relationships = self::getRelationships($mode);
        $withArray = [];

        foreach ($relationships as $key => $value) {
            if (is_string($key)) {
                $config = is_array($value) ? $value : [];
                $relationship = $key;

                if (!empty($config['with'])) {
                    $withArray[$relationship] = function ($query) use ($config) {
                        $query->with($config['with']);
                    };
                } else {
                    $withArray[] = $relationship;
                    if (isset($config['limit'])) {
                        $withArray[$relationship] = function ($query) use ($config) {
                            $query->limit($config['limit']);
                        };
                    }
                }
            } else {
                $withArray[] = $value;
            }
        }

        return $withArray;
    }
}
