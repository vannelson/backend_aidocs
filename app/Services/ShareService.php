<?php

namespace App\Services;

use App\Http\Resources\Document\DocumentShareResource;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\DocumentShareRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\ShareServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class ShareService implements ShareServiceInterface
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected DocumentShareRepositoryInterface $documentShareRepository,
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function share(int $documentId, int $ownerId, array $data): array
    {
        $document = $this->documentRepository->findById($documentId);

        if ($document->owner_id !== $ownerId) {
            throw new AuthorizationException('Only the document owner can share this document.');
        }

        $shares = collect($data['user_ids'])
            ->map(function (int $userId) use ($documentId, $ownerId, $data) {
                $targetUser = $this->userRepository->findById($userId);

                if ($targetUser->id === $ownerId) {
                    throw new InvalidArgumentException('The owner already has access to this document.');
                }

                return $this->documentShareRepository
                    ->upsertShare($documentId, $targetUser->id, $data['role'])
                    ->load('user');
            });

        return [
            'role' => $data['role'],
            'count' => $shares->count(),
            'users' => $shares
                ->map(fn ($share) => (new DocumentShareResource($share))->resolve()['user'])
                ->values()
                ->all(),
        ];
    }
}
