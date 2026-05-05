<?php

namespace App\Services;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function getShareableUsers(int $currentUserId): array
    {
        return $this->userRepository
            ->getShareableUsers($currentUserId)
            ->map(fn (User $user) => (new UserResource($user))->resolve())
            ->all();
    }
}
