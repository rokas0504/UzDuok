<?php

namespace App\Repositories\Users;

use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Repositories\Repository;

class UserRepository extends Repository
{
    public function __construct()
    {
        $this->model = new User();
    }

    /**
     * Create a new user in the database.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return $this->model::query()->create($data);
    }

    /**
     * Find a user by email address.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model::query()->where('email', $email)->first();
    }

    /**
     * Find a role by its slug.
     *
     * @param string $slug
     * @return Role
     */
    public function findRoleBySlug(string $slug): Role
    {
        return Role::where('slug', $slug)->firstOrFail();
    }
}
