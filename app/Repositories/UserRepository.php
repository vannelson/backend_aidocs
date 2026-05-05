<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getShareableUsers(int $currentUserId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('id', '!=', $currentUserId)
            ->orderBy('name')
            ->get();
    }
}
