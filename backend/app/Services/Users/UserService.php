<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use App\Services\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserService extends Service
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        $this->repository = $userRepository;
    }

    /**
     * Register a new user and log them in via session.
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $role = $this->userRepository->findRoleBySlug($data['role']);

        $user = $this->userRepository->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
        ]);

        $user->load('role');

        // Log the user in via session
        //auth()->login($user);

        return $user;
    }

    /**
     * Authenticate a user via session.
     *
     * @param array $credentials
     * @return User
     * @throws ValidationException
     */
    public function login(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->load('role');

        // Log the user in via session
        auth()->login($user);

        return $user;
    }

    /**
     * Log out the authenticated user.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        auth()->guard('web')->logout();

        // Invalidate the session
        request()->session()->invalidate();

        // Regenerate the CSRF token
        request()->session()->regenerateToken();
    }

    /**
     * Get all child users.
     *
     * @return Collection
     */
    public function getChildUsers(): collection
    {
        return $this->userRepository->getChildUsers();
    }
}
