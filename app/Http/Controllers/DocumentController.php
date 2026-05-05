<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\ImportDocumentRequest;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Services\Contracts\DocumentServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DocumentController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected DocumentServiceInterface $documentService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            return $this->success(
                'Documents retrieved successfully!',
                $this->documentService->getList($request->user()->id)
            );
        } catch (Throwable $exception) {
            return $this->error('Failed to load documents.', 500);
        }
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            return $this->success(
                'Document created successfully!',
                $this->documentService->create($request->user()->id, $request->validated()),
                201
            );
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            return $this->error('Failed to create document.', 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            return $this->success(
                'Document retrieved successfully!',
                $this->documentService->detail($id, $request->user()->id)
            );
        } catch (ModelNotFoundException $exception) {
            return $this->error('Document not found.', 404);
        } catch (Throwable $exception) {
            return $this->error('Failed to load document.', 500);
        }
    }

    public function update(UpdateDocumentRequest $request, int $id): JsonResponse
    {
        try {
            return $this->success(
                'Document updated successfully!',
                $this->documentService->update($id, $request->user()->id, $request->validated())
            );
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), 403);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Document not found.', 404);
        } catch (Throwable $exception) {
            return $this->error('Failed to update document.', 500);
        }
    }

    public function import(ImportDocumentRequest $request): JsonResponse
    {
        try {
            return $this->success(
                'Document imported successfully!',
                $this->documentService->import($request->user()->id, $request->file('file')),
                201
            );
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            return $this->error('Failed to import document.', 500);
        }
    }

    public function exportPdf(Request $request, int $id): StreamedResponse|JsonResponse
    {
        try {
            return $this->documentService->exportPdf($id, $request->user()->id);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Document not found.', 404);
        } catch (Throwable $exception) {
            return $this->error('Failed to export document as PDF.', 500);
        }
    }

    public function exportWord(Request $request, int $id): StreamedResponse|JsonResponse
    {
        try {
            return $this->documentService->exportWord($id, $request->user()->id);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Document not found.', 404);
        } catch (Throwable $exception) {
            return $this->error('Failed to export document as Word.', 500);
        }
    }
}
