<?php

namespace App\Http\Controllers;

use App\Http\Requests\Share\ShareDocumentRequest;
use App\Services\Contracts\ShareServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class ShareController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected ShareServiceInterface $shareService
    ) {
    }

    public function store(ShareDocumentRequest $request, int $id): JsonResponse
    {
        try {
            return $this->success(
                'Document shared successfully!',
                $this->shareService->share($id, $request->user()->id, $request->validated())
            );
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (AuthorizationException $exception) {
            return $this->error($exception->getMessage(), 403);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Document or user not found.', 404);
        } catch (Throwable $exception) {
            return $this->error('Failed to share document.', 500);
        }
    }
}
