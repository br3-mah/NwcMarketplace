<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Childcategory;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()->with($this->defaultRelations());

        $this->applyFilters($query, $filters);
        $this->applySorting($query, Arr::get($filters, 'sort'));

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        return $query->paginate($perPage);
    }

    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this->paginate($filters);
    }

    public function create(array $payload): Product
    {
        return DB::transaction(function () use ($payload) {
            $product = new Product();

            $this->fillProduct($product, $payload);
            $product->save();

            $this->assignCategories($product, Arr::get($payload, 'category_ids', []));
            $this->syncImages($product, Arr::get($payload, 'images', []));

            return $product->fresh()->load($this->defaultRelations());
        });
    }

    public function update(Product $product, array $payload): Product
    {
        return DB::transaction(function () use ($product, $payload) {
            $this->fillProduct($product, $payload);
            $product->save();

            if (array_key_exists('category_ids', $payload)) {
                $this->assignCategories($product, $payload['category_ids'] ?? []);
            }

            if (array_key_exists('images', $payload)) {
                $product->galleries()->delete();
                $this->syncImages($product, $payload['images'] ?? []);
            }

            return $product->fresh()->load($this->defaultRelations());
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->galleries()->delete();
            $product->delete();
        });
    }

    public function updateInventory(Product $product, array $payload): Product
    {
        if (array_key_exists('stock_qty', $payload)) {
            $product->stock = (int) $payload['stock_qty'];
        }

        if (array_key_exists('low_stock_threshold', $payload)) {
            $product->minimum_qty = $payload['low_stock_threshold'] !== null
                ? (int) $payload['low_stock_threshold']
                : null;
        }

        $product->save();

        return $product->fresh();
    }

    public function inventoryData(Product $product): array
    {
        $product->refresh();

        return [
            'stock_qty' => (int) ($product->stock ?? 0),
            'low_stock_threshold' => $product->minimum_qty !== null ? (int) $product->minimum_qty : null,
        ];
    }

    public function addImage(Product $product, array $payload): Gallery
    {
        return $product->galleries()->create([
            'photo' => $payload['file_id'],
        ]);
    }

    public function defaultRelations(): array
    {
        return ['category', 'subcategory', 'childcategory', 'galleries'];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['sellerId'])) {
            $query->where('user_id', $filters['sellerId']);
        }

        if (!empty($filters['category'])) {
            $category = $filters['category'];

            $query->where(function (Builder $builder) use ($category) {
                $builder->when(is_numeric($category), function (Builder $inner) use ($category) {
                    $inner->where('category_id', $category)
                        ->orWhere('subcategory_id', $category)
                        ->orWhere('childcategory_id', $category);
                }, function (Builder $inner) use ($category) {
                    $inner->whereHas('category', fn (Builder $cat) => $cat->where('slug', $category))
                        ->orWhereHas('subcategory', fn (Builder $sub) => $sub->where('slug', $category))
                        ->orWhereHas('childcategory', fn (Builder $child) => $child->where('slug', $category));
                });
            });
        }

        if (!empty($filters['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $filters['q']) . '%';

            $query->where(function (Builder $builder) use ($term) {
                $builder->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if (!empty($filters['min'])) {
            $query->where('price', '>=', $filters['min']);
        }

        if (!empty($filters['max'])) {
            $query->where('price', '<=', $filters['max']);
        }
    }

    private function applySorting(Builder $query, ?string $sort): void
    {
        $sort = $sort ? strtolower($sort) : null;

        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'asc' => $query->orderBy('created_at', 'asc'),
            'desc' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    private function fillProduct(Product $product, array $payload): void
    {
        if (array_key_exists('seller_id', $payload)) {
            $product->user_id = (int) $payload['seller_id'];
        }

        if (array_key_exists('title', $payload)) {
            $product->name = $payload['title'];
            $product->slug = $this->generateUniqueSlug($payload['title'], $product->id);
        }

        if (array_key_exists('description', $payload)) {
            $product->details = $payload['description'] ?? '';
        }

        if (array_key_exists('price', $payload)) {
            $product->price = $payload['price'];
            if (!$product->previous_price) {
                $product->previous_price = $payload['price'];
            }
        }

        if (array_key_exists('stock_qty', $payload)) {
            $product->stock = (int) $payload['stock_qty'];
        }

        if (array_key_exists('sku', $payload)) {
            $product->sku = $payload['sku'];
        }

        if ($product->type === null) {
            $product->type = 'Physical';
        }

        if ($product->status === null) {
            $product->status = 1;
        }
    }

    private function assignCategories(Product $product, array $categoryIds): void
    {
        $categoryIds = array_values(array_filter($categoryIds, fn ($id) => $id !== null && $id !== ''));

        $product->category_id = $this->resolveCategoryId($categoryIds[0] ?? null);
        $product->subcategory_id = $this->resolveSubcategoryId($categoryIds[1] ?? null);
        $product->childcategory_id = $this->resolveChildcategoryId($categoryIds[2] ?? null);
        $product->save();
    }

    private function syncImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if (!$image) {
                continue;
            }

            $product->galleries()->create([
                'photo' => $image,
            ]);
        }
    }

    private function resolveCategoryId($id): ?int
    {
        if (!$id) {
            return null;
        }

        return Category::whereKey($id)->value('id');
    }

    private function resolveSubcategoryId($id): ?int
    {
        if (!$id) {
            return null;
        }

        return Subcategory::whereKey($id)->value('id');
    }

    private function resolveChildcategoryId($id): ?int
    {
        if (!$id) {
            return null;
        }

        return Childcategory::whereKey($id)->value('id');
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Product::where('slug', $slug)
            ->when($ignoreId, fn (Builder $builder) => $builder->where('id', '!=', $ignoreId))
            ->exists();
    }
}

