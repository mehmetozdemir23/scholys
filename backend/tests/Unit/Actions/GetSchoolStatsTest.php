<?php

declare(strict_types=1);

use App\Actions\GetSchoolStats;
use App\Models\ClassGroup;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('GetSchoolStats returns correct structure', function (): void {
    $superAdmin = createSuperAdmin();

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['total_students', 'total_teachers', 'total_classes']);
});

test('GetSchoolStats counts users correctly by role', function (): void {
    $superAdmin = createSuperAdmin();
    $school = $superAdmin->school;

    createTeacher($school->id);
    createTeacher($school->id);
    createStudent($school->id);
    createStudent($school->id);
    createStudent($school->id);

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result['total_teachers'])->toBe(2)
        ->and($result['total_students'])->toBe(3);
});

test('GetSchoolStats counts classes for current academic year only', function (): void {
    $superAdmin = createSuperAdmin();
    $school = $superAdmin->school;

    ClassGroup::factory()->count(3)->create([
        'school_id' => $school->id,
        'academic_year' => getCurrentAcademicYear(),
    ]);

    ClassGroup::factory()->count(2)->create([
        'school_id' => $school->id,
        'academic_year' => '2022-2023',
    ]);

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result['total_classes'])->toBe(3);
});

test('GetSchoolStats filters by user school', function (): void {
    $superAdmin1 = createSuperAdmin();
    $superAdmin2 = createSuperAdmin();

    createStudent($superAdmin1->school->id);
    createTeacher($superAdmin1->school->id);

    createStudent($superAdmin2->school->id);
    createTeacher($superAdmin2->school->id);

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin1);

    expect($result['total_students'])->toBe(1)
        ->and($result['total_teachers'])->toBe(1);
});
