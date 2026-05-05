<?php

namespace App\Services\Contracts;

interface ShareServiceInterface
{
    public function share(int $documentId, int $ownerId, array $data): array;

    public function getShares(int $documentId, int $ownerId): array;

    public function updateShare(int $documentId, int $shareId, int $ownerId, array $data): array;
}
