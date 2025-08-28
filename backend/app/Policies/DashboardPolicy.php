<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class DashboardPolicy
{
    public function viewSchoolStats(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('teacher');
    }
}
