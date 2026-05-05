<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait ResponseTrait
{
    protected function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        $payload = [
            'status' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function validationError(ValidationException $exception): JsonResponse
    {
        return $this->error('Validation failed.', 422, $exception->errors());
    }

    protected function successLogin(array $user, string $token, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Login successful!',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], $status);
    }
}
