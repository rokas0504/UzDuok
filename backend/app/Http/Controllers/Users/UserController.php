<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Get all child users.
     */
    public function getChildren(): JsonResponse
    {
        $children = $this->userService->getChildUsers();

        return response()->json([
            'users' => $children->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'points' => $user->points,
            ]),
        ]);
    }
}