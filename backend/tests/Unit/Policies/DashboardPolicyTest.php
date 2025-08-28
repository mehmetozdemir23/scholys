<?php

declare(strict_types=1);

use App\Policies\DashboardPolicy;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('super admin can view school stats', function (): void {
    $superAdmin = createSuperAdmin();

    $policy = new DashboardPolicy();
    $result = $policy->viewSchoolStats($superAdmin);

    expect($result)->toBeTrue();
});

test('admin can view school stats', function (): void {
    $admin = createAdmin();

    $policy = new DashboardPolicy();
    $result = $policy->viewSchoolStats($admin);

    expect($result)->toBeTrue();
});

test('teacher can view school stats', function (): void {
    $teacher = createTeacher();

    $policy = new DashboardPolicy();
    $result = $policy->viewSchoolStats($teacher);

    expect($result)->toBeTrue();
});

test('student cannot view school stats', function (): void {
    $student = createStudent();

    $policy = new DashboardPolicy();
    $result = $policy->viewSchoolStats($student);

    expect($result)->toBeFalse();
});

test('user without roles cannot view school stats', function (): void {
    $user = createSuperAdmin();
    $user->roles()->detach();

    $policy = new DashboardPolicy();
    $result = $policy->viewSchoolStats($user);

    expect($result)->toBeFalse();
});
