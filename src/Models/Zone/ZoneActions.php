<?php

namespace Kennofizet\PackagesCore\Models\Zone;

use Kennofizet\PackagesCore\Models\Zone;

trait ZoneActions
{
    public static function findById(int $id): ?Zone
    {
        return Zone::find($id);
    }
}
