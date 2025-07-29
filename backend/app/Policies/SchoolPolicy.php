<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\School;
use App\Models\User;

final class SchoolPolicy
{
    /**
     * Determine whether the user can update the school.
     */
    public function update(User $user, School $school): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN) && $school->id === $user->school_id;
    }
}
