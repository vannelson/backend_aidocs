<?php

namespace App\Services;

use App\Http\Resources\Document\DocumentResource;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\DocumentShareRepositoryInterface;
use App\Services\Contracts\DocumentServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentService implements DocumentServiceInterface
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected DocumentShareRepositoryInterface $documentShareRepository
    ) {
    }

    public function getList(int $userId): array
    {
        $owned = $this->markDocuments(
            $this->documentRepository->getOwnedByUser($userId),
            'owned',
            'owner'
        );

        $shared = $this->markDocuments(
            $this->documentRepository->getSharedWithUser($userId),
            'shared'
        );

        return [
            'owned_documents' => $this->resourceArray($owned),
            'shared_documents' => $this->resourceArray($shared),
        ];
    }

    public function create(int $userId, array $data): array
    {
        $document = $this->documentRepository->create([
            'owner_id' => $userId,
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
        ])->load('owner');

        $document->setAttribute('ownership_type', 'owned');
        $document->setAttribute('access_role', 'owner');

        return (new DocumentResource($document))->resolve();
    }

    public function detail(int $documentId, int $userId): array
    {
        $document = $this->documentRepository->findAccessibleById($documentId, $userId);

        if (!$document) {
            throw new ModelNotFoundException('Document not found.');
        }

        $this->applyAccessMetadata($document, $userId);

        return (new DocumentResource($document))->resolve();
    }

    public function update(int $documentId, int $userId, array $data): array
    {
        $document = $this->documentRepository->findAccessibleById($documentId, $userId);

        if (!$document) {
            throw new ModelNotFoundException('Document not found.');
        }

        $role = $this->resolveRole($document, $userId);
        if (!in_array($role, ['owner', 'editor'], true)) {
            throw new AuthorizationException('You do not have permission to edit this document.');
        }

        $updatedDocument = $this->documentRepository
            ->updateAndGet($documentId, $data)
            ->load([
                'owner',
                'shares' => fn ($query) => $query->where('user_id', $userId),
            ]);

        $this->applyAccessMetadata($updatedDocument, $userId);

        return (new DocumentResource($updatedDocument))->resolve();
    }

    public function import(int $userId, UploadedFile $file): array
    {
        $contents = $file->get();
        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return $this->create($userId, [
            'title' => Str::of($title)->trim()->value() ?: 'Imported Document',
            'content' => $this->plainTextToHtml($contents),
        ]);
    }

    public function delete(int $documentId, int $userId): void
    {
        $document = $this->documentRepository->findById($documentId);

        if ($document->owner_id !== $userId) {
            throw new AuthorizationException('Only the document owner can delete this document.');
        }

        $document->shares()->delete();
        $this->documentRepository->delete($documentId);
    }

    protected function resourceArray(Collection $documents): array
    {
        return $documents
            ->map(fn (Document $document) => (new DocumentResource($document))->resolve())
            ->all();
    }

    protected function markDocuments(Collection $documents, string $ownershipType, ?string $defaultRole = null): Collection
    {
        return $documents->map(function (Document $document) use ($ownershipType, $defaultRole) {
            $document->setAttribute('ownership_type', $ownershipType);
            $document->setAttribute('access_role', $defaultRole ?? $document->shares->first()?->role);

            return $document;
        });
    }

    protected function applyAccessMetadata(Document $document, int $userId): void
    {
        $role = $this->resolveRole($document, $userId);

        $document->setAttribute('ownership_type', $document->owner_id === $userId ? 'owned' : 'shared');
        $document->setAttribute('access_role', $role);
    }

    protected function resolveRole(Document $document, int $userId): ?string
    {
        if ($document->owner_id === $userId) {
            return 'owner';
        }

        return $document->shares->first()?->role;
    }

    protected function plainTextToHtml(string $contents): string
    {
        $escaped = e($contents);

        return '<p>' . str_replace(PHP_EOL, '</p><p>', nl2br($escaped, false)) . '</p>';
    }

}
