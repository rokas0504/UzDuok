<?php

namespace App\Repositories\Users;

use App\Models\Users\User;
use App\Repositories\Repository;

class UserRepository extends Repository
{
    public function __construct()
    {
        $this->model = new User();
    }
}
