<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Only parents can register new users
        if (!$request->user()->isParent()) {
            return response()->json([
                'message' => 'Unauthorized. Only parents can register new users.',
                ], 403);
        }
        $user = $this->userService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->slug,
                'points' => $user->points,
            ],
        ], 201);
    }

    /**
     * Login an existing user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->userService->login($request->validated());

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->slug,
                'points' => $user->points,
            ],
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        try {
            $this->userService->logout($user);

            return response()->json([
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the authenticated user's profile.
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user->load('role');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->slug,
                'points' => $user->points,
            ],
        ]);
    }
}
