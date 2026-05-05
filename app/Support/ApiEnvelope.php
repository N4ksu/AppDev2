<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiEnvelope
{
    public static function success(string $code, string $message, array $data = [], int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    public static function error(
        string $status,
        string $code,
        string $message,
        array $data = [],
        int $httpStatus = 400
    ): JsonResponse {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }
}
