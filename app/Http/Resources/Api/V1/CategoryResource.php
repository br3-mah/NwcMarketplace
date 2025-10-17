<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (int) $this->status,
            'status_label' => $this->resolveStatusLabel(),
            'language_id' => $this->language_id ? (string) $this->language_id : null,
        ];
    }

    private function resolveStatusLabel(): string
    {
        return (int) $this->status === 1 ? 'active' : 'inactive';
    }
}

