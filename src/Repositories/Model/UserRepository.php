<?php

namespace Kennofizet\PackagesCore\Repositories\Model;

use Kennofizet\PackagesCore\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }
}
