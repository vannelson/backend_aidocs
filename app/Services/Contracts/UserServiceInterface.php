<?php

namespace App\Services\Contracts;

interface UserServiceInterface
{
    public function getShareableUsers(int $currentUserId): array;
}
