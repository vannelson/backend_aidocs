<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Contracts\AuthServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected AuthServiceInterface $authService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successLogin($result['user'], $result['token'], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            return $this->error('Failed to register user.', 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return $this->successLogin($result['user'], $result['token']);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (AuthenticationException $exception) {
            return $this->error($exception->getMessage(), 401);
        } catch (Throwable $exception) {
            return $this->error('Failed to login.', 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return $this->success(
                'Authenticated user retrieved successfully!',
                $this->authService->me($request->user())
            );
        } catch (Throwable $exception) {
            return $this->error('Failed to load authenticated user.', 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->success('Logout successful!');
        } catch (Throwable $exception) {
            return $this->error('Failed to logout.', 500);
        }
    }
}
