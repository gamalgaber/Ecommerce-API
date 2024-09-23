<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

trait Response
{
    public function sendResponse(string $message = '', bool $status = true, array|object $data = [], int $statusCode = 200): JsonResponse
    {
        // Convert data array to object if it's an array
        $responseData = empty($data) ? (object)[] : (object)$data;

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $responseData
        ], $statusCode);
    }

    protected function respondWithToken(string $token, bool $status = true, string $message = '', array|object $data = []): JsonResponse
    {
        // Convert data array to object if it's an array
        $responseData = empty($data) ? (object)[] : (object)$data;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // Return TTL in seconds
            'status' => $status,
            'message' => $message,
            'data' => $responseData
        ]);
    }
}
