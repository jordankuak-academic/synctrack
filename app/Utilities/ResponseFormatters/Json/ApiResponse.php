<?php
namespace App\Utilities\ResponseFormatters\Json;

use Illuminate\Http\JsonResponse;

trait ApiResponse {
    protected function successResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse {
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data,
        ], $statusCode);
    }
    
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse {
        return response()->json([
            "status" => false,
            "message" => $message,
        ], $statusCode);
    }
}
