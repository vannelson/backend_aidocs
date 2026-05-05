<?php

namespace App\Repositories;

use App\Models\DocumentShare;
use App\Repositories\Contracts\DocumentShareRepositoryInterface;
use Illuminate\Support\Collection;

class DocumentShareRepository extends BaseRepository implements DocumentShareRepositoryInterface
{
    public function __construct(DocumentShare $documentShare)
    {
        parent::__construct($documentShare);
    }

    public function upsertShare(int $documentId, int $userId, string $role): DocumentShare
    {
        return $this->model->updateOrCreate(
            [
                'document_id' => $documentId,
                'user_id' => $userId,
            ],
            [
                'role' => $role,
            ]
        );
    }

    public function findByDocumentAndUser(int $documentId, int $userId): ?DocumentShare
    {
        return $this->model
            ->newQuery()
            ->where('document_id', $documentId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getByDocumentId(int $documentId): Collection
    {
        return $this->model
            ->newQuery()
            ->with('user')
            ->where('document_id', $documentId)
            ->latest('updated_at')
            ->get();
    }

    public function findByIdAndDocument(int $shareId, int $documentId): ?DocumentShare
    {
        return $this->model
            ->newQuery()
            ->with('user')
            ->where('id', $shareId)
            ->where('document_id', $documentId)
            ->first();
    }
}
