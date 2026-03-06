<?php

namespace Kennofizet\PackagesCore\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Kennofizet\PackagesCore\Models\User;
use Kennofizet\PackagesCore\Models\Zone;
use Kennofizet\PackagesCore\Models\ZoneUser\ZoneUserScopes;
use Kennofizet\PackagesCore\Core\Model\BaseModelActions;

class ZoneUser extends Pivot
{
    use ZoneUserScopes;
    use BaseModelActions;

    public function getTable()
    {
        return self::getPivotTableName('knf_core_zone_users');
    }

    protected $fillable = [
        'user_id',
        'zone_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
