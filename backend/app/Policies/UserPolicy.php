<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

final class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }

    public function update(User $user): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }
}
