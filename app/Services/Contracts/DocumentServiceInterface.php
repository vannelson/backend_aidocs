<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface DocumentServiceInterface
{
    public function getList(int $userId): array;

    public function create(int $userId, array $data): array;

    public function detail(int $documentId, int $userId): array;

    public function update(int $documentId, int $userId, array $data): array;

    public function import(int $userId, UploadedFile $file): array;

    public function delete(int $documentId, int $userId): void;
}
