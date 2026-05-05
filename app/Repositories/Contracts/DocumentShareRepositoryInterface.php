<?php

namespace App\Repositories\Contracts;

use App\Models\DocumentShare;
use Illuminate\Support\Collection;

interface DocumentShareRepositoryInterface extends RepositoryInterface
{
    public function upsertShare(int $documentId, int $userId, string $role): DocumentShare;

    public function findByDocumentAndUser(int $documentId, int $userId): ?DocumentShare;

    public function getByDocumentId(int $documentId): Collection;

    public function findByIdAndDocument(int $shareId, int $documentId): ?DocumentShare;
}
