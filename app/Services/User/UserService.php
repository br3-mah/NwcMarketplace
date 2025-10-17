<?php

namespace App\Services\User;

use App\Helpers\AuthHelper;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function paginateUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['latestVerification']);

        if (!empty($filters['q'])) {
            $this->applySearchFilter($query, (string) $filters['q']);
        }

        if (!empty($filters['role'])) {
            $this->applyRoleFilter($query, (string) $filters['role']);
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $this->applyStatusFilter($query, $filters['status']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query
            ->orderByDesc('id')
            ->paginate($perPage > 0 ? $perPage : 15);
    }

    public function loadUserRelations(User $user): User
    {
        return $user->loadMissing('latestVerification');
    }

    public function updateUser(User $user, array $payload): User
    {
        if (array_key_exists('fname', $payload)) {
            $user->fname = $payload['fname'] !== null ? trim((string) $payload['fname']) : null;
        }

        if (array_key_exists('lname', $payload)) {
            $user->lname = $payload['lname'] !== null ? trim((string) $payload['lname']) : null;
        }

        if (array_key_exists('gender', $payload)) {
            $user->gender = AuthHelper::normalizeGender($payload['gender']);
        }

        if (array_key_exists('email', $payload)) {
            $user->email = AuthHelper::normalizeEmail($payload['email']);
        }

        if (array_key_exists('phone', $payload)) {
            $user->phone = AuthHelper::normalizePhone($payload['phone']);
        }

        $user->name = AuthHelper::fullName($user->fname, $user->lname);

        $user->save();

        return $this->loadUserRelations($user->fresh());
    }

    private function applySearchFilter(Builder $query, string $term): void
    {
        $likeTerm = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';

        $query->where(function (Builder $inner) use ($likeTerm) {
            $inner->where('name', 'like', $likeTerm)
                ->orWhere('fname', 'like', $likeTerm)
                ->orWhere('lname', 'like', $likeTerm)
                ->orWhere('email', 'like', $likeTerm)
                ->orWhere('phone', 'like', $likeTerm);
        });
    }

    private function applyRoleFilter(Builder $query, string $role): void
    {
        $normalized = strtolower(trim($role));

        match ($normalized) {
            'vendor', 'seller' => $query->where('is_vendor', 2),
            'customer', 'buyer', 'user' => $query->where(function (Builder $inner) {
                $inner->whereNull('is_vendor')->orWhere('is_vendor', '!=', 2);
            }),
            'provider' => $query->where('is_provider', 1),
            default => null,
        };
    }

    private function applyStatusFilter(Builder $query, $status): void
    {
        if (is_numeric($status)) {
            $query->where('status', (int) $status);
            return;
        }

        $normalized = strtolower(trim((string) $status));

        $statusMap = [
            'active' => 1,
            'inactive' => 0,
            'pending' => 0,
            'suspended' => 2,
            'banned' => 2,
        ];

        if (array_key_exists($normalized, $statusMap)) {
            $query->where('status', $statusMap[$normalized]);
        }
    }
}

