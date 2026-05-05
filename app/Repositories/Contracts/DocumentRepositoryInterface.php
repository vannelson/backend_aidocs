<?php

namespace App\Repositories\Contracts;

use App\Models\Document;
use Illuminate\Support\Collection;

interface DocumentRepositoryInterface extends RepositoryInterface
{
    public function getOwnedByUser(int $userId): Collection;

    public function getSharedWithUser(int $userId): Collection;

    public function findAccessibleById(int $documentId, int $userId): ?Document;
}
