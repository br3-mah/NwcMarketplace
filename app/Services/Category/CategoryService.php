<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function listCategories(array $filters = []): Collection
    {
        $query = Category::query()->orderBy('name');

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $status = $this->normalizeStatus($filters['status']);

            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        if (!empty($filters['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $filters['q']) . '%';

            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        return $query->get();
    }

    public function createCategory(array $payload): Category
    {
        $category = new Category();
        $category->name = trim((string) $payload['name']);
        $category->slug = $this->generateUniqueSlug($payload['slug'] ?? null, $category->name);
        $category->status = $this->normalizeStatus($payload['status'] ?? 1) ?? 1;

        if (array_key_exists('language_id', $payload)) {
            $category->language_id = $payload['language_id'];
        }

        $category->save();

        return $category;
    }

    private function normalizeStatus($value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value === 1 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'active', 'enabled' => 1,
            '0', 'false', 'inactive', 'disabled' => 0,
            default => null,
        };
    }

    private function generateUniqueSlug(?string $providedSlug, string $fallback): string
    {
        $base = $providedSlug ? Str::slug($providedSlug) : Str::slug($fallback);
        $base = $base !== '' ? $base : Str::lower(Str::random(8));

        $slug = $base;
        $suffix = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}

