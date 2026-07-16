<?php
namespace App\Utilities\ResponseFormatters\Php;

trait ApiResponse {
    protected function successResponse(string $message, array $data = []): array {
        return [
            "status" => true,
            "message" => $message,
            "data" => $data,
        ];
    }
    
    protected function errorResponse(string $message): array {
        return [
            "status" => false,
            "message" => $message,
        ];
    }
}
