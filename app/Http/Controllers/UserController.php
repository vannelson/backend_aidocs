<?php

namespace App\Http\Controllers;

use App\Services\Contracts\UserServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected UserServiceInterface $userService
    ) {
    }

    public function shareable(Request $request): JsonResponse
    {
        try {
            return $this->success(
                'Shareable users retrieved successfully!',
                $this->userService->getShareableUsers($request->user()->id)
            );
        } catch (Throwable $exception) {
            return $this->error('Failed to load shareable users.', 500);
        }
    }
}
