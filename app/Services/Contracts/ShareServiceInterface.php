<?php

namespace App\Services\Contracts;

interface ShareServiceInterface
{
    public function share(int $documentId, int $ownerId, array $data): array;
}
