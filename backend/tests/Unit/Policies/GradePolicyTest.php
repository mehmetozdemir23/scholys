<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Grade;
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

test('teacher can update grade they created with correct context', function (): void {
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
    $result = $policy->update($teacher, $grade, $classGroup, $student, $subject);

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
    $result = $policy->update($teacher2, $grade, $classGroup, $student, $subject);

    expect($result)->toBeFalse();
});

test('teacher cannot update grade with inconsistent class group', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
    $otherClassGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade, $otherClassGroup, $student, $subject);

    expect($result)->toBeFalse();
});

test('teacher cannot update grade with inconsistent student', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $otherStudent = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade, $classGroup, $otherStudent, $subject);

    expect($result)->toBeFalse();
});

test('teacher cannot update grade with inconsistent subject', function (): void {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $otherSubject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
    ]);

    $policy = new GradePolicy();
    $result = $policy->update($teacher, $grade, $classGroup, $student, $otherSubject);

    expect($result)->toBeFalse();
});
