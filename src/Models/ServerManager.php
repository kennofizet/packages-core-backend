<?php

namespace Kennofizet\PackagesCore\Models;

use Kennofizet\PackagesCore\Core\Model\BaseModel;
use Kennofizet\PackagesCore\Models\ServerManager\ServerManagerActions;
use Kennofizet\PackagesCore\Models\ServerManager\ServerManagerRelations;
use Kennofizet\PackagesCore\Models\ServerManager\ServerManagerScopes;

class ServerManager extends BaseModel
{
    use ServerManagerRelations, ServerManagerActions, ServerManagerScopes;

    public function getTable()
    {
        return self::getPivotTableName('knf_core_zone_managers');
    }

    protected $fillable = [
        'user_id',
        'server_id',
    ];
}
