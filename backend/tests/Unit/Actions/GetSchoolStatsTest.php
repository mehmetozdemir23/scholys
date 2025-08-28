<?php

declare(strict_types=1);

use App\Actions\GetSchoolStats;
use App\Models\ClassGroup;
use App\Models\Grade;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('GetSchoolStats returns correct structure', function (): void {
    $superAdmin = createSuperAdmin();

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['total_students', 'total_teachers', 'total_classes', 'school_average']);
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

test('GetSchoolStats calculates school average from active grades only', function (): void {
    $superAdmin = createSuperAdmin();
    $school = $superAdmin->school;
    $student = createStudent($school->id);

    Grade::factory()->create([
        'student_id' => $student->id,
        'value' => 15.0,
        'is_active' => true,
    ]);

    Grade::factory()->create([
        'student_id' => $student->id,
        'value' => 10.0,
        'is_active' => false,
    ]);

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result['school_average'])->toBe(15.0);
});

test('GetSchoolStats returns zero average when no grades', function (): void {
    $superAdmin = createSuperAdmin();

    $action = new GetSchoolStats();
    $result = $action->handle($superAdmin);

    expect($result['school_average'])->toBe(0.0);
});
