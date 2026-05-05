<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface DocumentServiceInterface
{
    public function getList(int $userId): array;

    public function create(int $userId, array $data): array;

    public function detail(int $documentId, int $userId): array;

    public function update(int $documentId, int $userId, array $data): array;

    public function import(int $userId, UploadedFile $file): array;

    public function exportPdf(int $documentId, int $userId): StreamedResponse;

    public function exportWord(int $documentId, int $userId): StreamedResponse;
}
