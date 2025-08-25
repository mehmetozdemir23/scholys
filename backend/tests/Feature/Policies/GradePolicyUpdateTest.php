<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;
use App\Policies\GradePolicy;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('teacher can update grade they created', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade);

    expect($result)->toBeTrue();
});

test('teacher cannot update grade created by another teacher', function (): void {
    $teacher1 = createTeacher();
    $teacher2 = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher1->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher1->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher1->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher2, $grade);

    expect($result)->toBeFalse();
});

test('teacher cannot update grade if no longer assigned to subject', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade);

    expect($result)->toBeFalse();
});

test('teacher cannot update grade if student no longer in class', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade);

    expect($result)->toBeFalse();
});

test('non-teacher cannot update grades', function (): void {
    $admin = createSuperAdmin();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $admin->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $admin->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($admin, $grade);

    expect($result)->toBeFalse();
});