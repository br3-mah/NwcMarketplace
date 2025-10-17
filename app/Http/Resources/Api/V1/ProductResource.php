<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'seller_id' => $this->user_id ? (string) $this->user_id : null,
            'title' => $this->name,
            'slug' => $this->slug,
            'description' => $this->details,
            'sku' => $this->sku,
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->resolveCurrency(),
            'stock_qty' => (int) ($this->stock ?? 0),
            'low_stock_threshold' => $this->minimum_qty !== null ? (int) $this->minimum_qty : null,
            'status' => (int) ($this->status ?? 0),
            'is_published' => (int) ($this->status ?? 0) === 1,
            'categories' => $this->categoryPayload(),
            'images' => ProductImageResource::collection($this->whenLoaded('galleries')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveCurrency(): string
    {
        return config('app.currency', 'USD');
    }

    private function categoryPayload(): array
    {
        $categories = new Collection();

        if ($this->relationLoaded('category') && $this->category) {
            $categories->push([
                'id' => (string) $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'type' => 'category',
            ]);
        }

        if ($this->relationLoaded('subcategory') && $this->subcategory) {
            $categories->push([
                'id' => (string) $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
                'type' => 'subcategory',
            ]);
        }

        if ($this->relationLoaded('childcategory') && $this->childcategory) {
            $categories->push([
                'id' => (string) $this->childcategory->id,
                'name' => $this->childcategory->name,
                'slug' => $this->childcategory->slug,
                'type' => 'childcategory',
            ]);
        }

        return $categories->values()->all();
    }
}

