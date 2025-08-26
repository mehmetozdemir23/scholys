<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\School;
use App\Models\User;

function createUserWithRole(string $roleName, ?string $schoolId = null): User
{
    $school = $schoolId ? School::find($schoolId) : School::factory()->create();
    $role = Role::firstOrCreate(['name' => $roleName]);
    $user = User::factory()->create(['school_id' => $school->id]);
    $user->roles()->attach($role);

    return $user;
}

function createTeacher(?string $schoolId = null): User
{
    return createUserWithRole('teacher', $schoolId);
}

function createStudent(?string $schoolId = null): User
{
    return createUserWithRole('student', $schoolId);
}

function createSuperAdmin(?string $schoolId = null): User
{
    return createUserWithRole('super_admin', $schoolId);
}

function createAdmin(?string $schoolId = null): User
{
    return createUserWithRole('admin', $schoolId);
}
