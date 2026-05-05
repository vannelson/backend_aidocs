<?php

namespace App\Services;

use App\Http\Resources\Document\DocumentResource;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\DocumentShareRepositoryInterface;
use App\Services\Contracts\DocumentServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportPdf(int $documentId, int $userId): StreamedResponse
    {
        $document = $this->findAccessibleDocument($documentId, $userId);

        $pdf = Pdf::loadView('exports.document-pdf', [
            'document' => $document,
            'ownerName' => $document->owner?->name,
        ])->setPaper('a4');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            $this->buildExportFilename($document->title, 'pdf'),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportWord(int $documentId, int $userId): StreamedResponse
    {
        $document = $this->findAccessibleDocument($documentId, $userId);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
            'marginRight' => 900,
        ]);

        $section->addTitle($document->title ?: 'Untitled document', 1);

        if ($document->owner?->name) {
            $section->addText("Owner: {$document->owner->name}", ['color' => '6B7280', 'size' => 10]);
        }

        $section->addTextBreak(1);

        Html::addHtml($section, $this->buildWordHtml($document), false, false);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'gooddocs-word-');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($temporaryFile);

        return response()->streamDownload(
            function () use ($temporaryFile) {
                $stream = fopen($temporaryFile, 'rb');

                if ($stream !== false) {
                    fpassthru($stream);
                    fclose($stream);
                }

                @unlink($temporaryFile);
            },
            $this->buildExportFilename($document->title, 'docx'),
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
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

    protected function findAccessibleDocument(int $documentId, int $userId): Document
    {
        $document = $this->documentRepository->findAccessibleById($documentId, $userId);

        if (!$document) {
            throw new ModelNotFoundException('Document not found.');
        }

        return $document;
    }

    protected function buildExportFilename(string $title, string $extension): string
    {
        $safeTitle = Str::slug($title ?: 'untitled-document');

        return ($safeTitle ?: 'untitled-document') . '.' . $extension;
    }

    protected function buildWordHtml(Document $document): string
    {
        return sprintf(
            '<html><body>%s</body></html>',
            $document->content ?: '<p></p>'
        );
    }
}
