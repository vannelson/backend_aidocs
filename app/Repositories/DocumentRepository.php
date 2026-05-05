<?php

namespace App\Repositories;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface
{
    public function __construct(Document $document)
    {
        parent::__construct($document);
    }

    public function getOwnedByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with('owner')
            ->where('owner_id', $userId)
            ->latest('updated_at')
            ->get();
    }

    public function getSharedWithUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with([
                'owner',
                'shares' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->whereHas('shares', fn (Builder $query) => $query->where('user_id', $userId))
            ->latest('updated_at')
            ->get();
    }

    public function findAccessibleById(int $documentId, int $userId): ?Document
    {
        return $this->model
            ->newQuery()
            ->with([
                'owner',
                'shares' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->where('id', $documentId)
            ->where(function (Builder $query) use ($userId) {
                $query
                    ->where('owner_id', $userId)
                    ->orWhereHas('shares', fn (Builder $shareQuery) => $shareQuery->where('user_id', $userId));
            })
            ->first();
    }
}
