<?php

namespace Kennofizet\PackagesCore\Models\ServerManager;

use Kennofizet\PackagesCore\Models\ServerManager;
use Kennofizet\PackagesCore\Models\User;

trait ServerManagerActions
{
    public static function findByServerId(?int $serverId = null)
    {
        return ServerManager::byServer($serverId)->get();
    }

    public function getUser()
    {
        return User::findById($this->user_id);
    }
}
