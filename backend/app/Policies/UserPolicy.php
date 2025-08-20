<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function import(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
