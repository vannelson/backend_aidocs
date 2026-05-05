<?php

namespace App\Repositories\Contracts;

use App\Models\DocumentShare;

interface DocumentShareRepositoryInterface extends RepositoryInterface
{
    public function upsertShare(int $documentId, int $userId, string $role): DocumentShare;

    public function findByDocumentAndUser(int $documentId, int $userId): ?DocumentShare;
}
