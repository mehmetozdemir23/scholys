<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Policies\GradePolicy;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('teacher can create grade with valid entities', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $policy = new GradePolicy();
    $result = $policy->create($teacher, $classGroup, $student, $subject);

    expect($result)->toBeTrue();
});

test('teacher cannot create grade when not teaching subject', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $policy = new GradePolicy();
    $result = $policy->create($teacher, $classGroup, $student, $subject);

    expect($result)->toBeFalse();
});

test('teacher cannot create grade when student not in class', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $policy = new GradePolicy();
    $result = $policy->create($teacher, $classGroup, $student, $subject);

    expect($result)->toBeFalse();
});

test('teacher cannot create grade when not assigned to class', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $policy = new GradePolicy();
    $result = $policy->create($teacher, $classGroup, $student, $subject);

    expect($result)->toBeFalse();
});

test('non-teacher cannot create grade', function (): void {
    $admin = createSuperAdmin();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $admin->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);

    $policy = new GradePolicy();
    $result = $policy->create($admin, $classGroup, $student, $subject);

    expect($result)->toBeFalse();
});
