<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     * Server encountered an unexpected error
     * @return JsonResponse
     */
    protected function serverError(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('responses.server_error')
        ], 500);
    }

    /**
     * Resource updated successfully
     * @param string $message
     * @return JsonResponse
     */
    protected function updateSuccessful(string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = __('responses.resource_updated_successfully');
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Failed to create new entry do to unforeseen data check
     * @param string | array $message
     * @return JsonResponse
     */
    protected function unprocessableEntity(string|array $message): JsonResponse
    {
        $response = [
            'success' => false
        ];

        // Early return pattern - eliminates else clause
        if (is_array($message)) {
            return response()->json(array_merge($response, $message), 422);
        }

        $response['message'] = $message;
        return response()->json($response, 422);
    }

    /**
     * Request successful. Returns 200 status - OK
     * @param string|array $dataToReturn
     * @return JsonResponse
     */
    protected function successResponse(string|array $dataToReturn = array()): JsonResponse
    {
        $success = [
            'success' => true
        ];

        if (empty($dataToReturn)) {
            return response()->json($success);
        }

        // Early return pattern - eliminates else clause
        if (is_array($dataToReturn)) {
            return response()->json(array_merge($success, $dataToReturn));
        }

        $success['message'] = $dataToReturn;
        return response()->json($success);
    }

    /**
     * Unauthorized access
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorized(string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = __('responses.unauthorized');
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ], 401);
    }

    /**
     * Too many requests
     * @param string $message
     * @return JsonResponse
     */
    protected function tooManyRequests(string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = __('responses.too_many_attempts');
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }

    /**
     * Record created
     * @param string $message
     * @return JsonResponse
     */
    protected function created(string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = __('responses.resource_created_successfully');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 201);
    }

    /**
     * Bad request ex. expired token provided
     * @param string $message
     * @return JsonResponse
     */
    protected function badRequest(string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = __('responses.bad_request');
        }

        return response()->json([
            'success' => false,
            'message' => $message
        ], 422);
    }
}
