<?php

namespace App\Services\Users;

use App\Repositories\Users\UserRepository;
use App\Services\Service;

class UserService extends Service
{
    public function __construct()
    {
        $this->repository = new UserRepository();
    }
}
