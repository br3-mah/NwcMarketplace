<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\User;

trait AuthorizesRoles
{
    protected function ensureAdmin(User $user): void
    {
        $isAdmin = $user->roles()
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Administrator permissions required.');
        }
    }

    protected function ensureSeller(User $user): void
    {
        $isSeller = $user->roles()
            ->whereIn('name', ['Vendor', 'Seller'])
            ->exists();

        if (!$isSeller) {
            abort(403, 'Seller permissions required.');
        }
    }
}

