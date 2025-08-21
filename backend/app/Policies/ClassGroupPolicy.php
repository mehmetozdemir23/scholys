<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassGroup;
use App\Models\User;

final class ClassGroupPolicy
{
    /**
     * Determine whether the user can view any class groups.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can view the class group.
     */
    public function view(User $user, ClassGroup $classGroup): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $user->school_id === $classGroup->school_id;
        }

        if ($user->hasRole('teacher')) {
            return $user->school_id === $classGroup->school_id &&
                $classGroup->teachers()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('student')) {
            return $user->school_id === $classGroup->school_id &&
                $classGroup->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create class groups.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the class group.
     */
    public function update(User $user, ClassGroup $classGroup): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $user->school_id === $classGroup->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the class group.
     */
    public function delete(User $user, ClassGroup $classGroup): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $user->school_id === $classGroup->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can assign students to the class group.
     */
    public function assignStudent(User $user, ClassGroup $classGroup): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $user->school_id === $classGroup->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can assign teachers to the class group.
     */
    public function assignTeacher(User $user, ClassGroup $classGroup): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $user->school_id === $classGroup->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view class group statistics.
     */
    public function viewStats(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('admin');
    }
}
