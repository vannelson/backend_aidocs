<?php

namespace App\Services;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('gooddocs-token')->plainTextToken;

        return [
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
        ];
    }

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid Credential');
        }

        $user->tokens()->delete();
        $token = $user->createToken('gooddocs-token')->plainTextToken;

        return [
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
        ];
    }

    public function me(User $user): array
    {
        return (new UserResource($user))->resolve();
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
