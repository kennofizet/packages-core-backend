<?php

namespace Kennofizet\PackagesCore\Models\Token;

use Kennofizet\PackagesCore\Models\User;

trait TokenActions
{
    public function getUser()
    {
        return User::findById($this->user_id);
    }
}
