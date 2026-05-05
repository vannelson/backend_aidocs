<?php

namespace App\Http\Resources\Document;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'owner_id' => $this->owner_id,
            'ownership_type' => $this->ownership_type,
            'access_role' => $this->access_role,
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
