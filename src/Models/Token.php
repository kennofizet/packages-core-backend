<?php

namespace Kennofizet\PackagesCore\Models;

use Kennofizet\PackagesCore\Core\Model\BaseModel;
use Kennofizet\PackagesCore\Models\Token\TokenRelations;
use Kennofizet\PackagesCore\Models\Token\TokenScopes;
use Kennofizet\PackagesCore\Models\Token\TokenActions;

class Token extends BaseModel
{
    use TokenRelations, TokenActions, TokenScopes;

    public function getTable()
    {
        return self::getPivotTableName('knf_core_tokens');
    }

    protected $fillable = [
        'user_id',
        'token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
