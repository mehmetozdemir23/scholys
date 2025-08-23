<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;

require_once __DIR__.'/../../../Helpers/TestHelpers.php';

test('teacher can create grade for student in their class and subject', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $gradeData = [
        'value' => 15.50,
        'max_value' => 20.00,
        'coefficient' => 1.00,
        'title' => 'Contrôle Chapitre 1',
        'comment' => 'Bon travail',
        'given_at' => '2024-01-15',
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/notes", $gradeData);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Note ajoutée avec succès!']);

    expect(Grade::count())->toBe(1);

    $grade = Grade::first();
    expect($grade->student_id)->toBe($student->id);
    expect($grade->teacher_id)->toBe($teacher->id);
    expect($grade->subject_id)->toBe($subject->id);
    expect($grade->class_group_id)->toBe($classGroup->id);
    expect($grade->value)->toBe('15.50');
});

test('teacher cannot create grade for student not in their class', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $gradeData = [
        'value' => 15.50,
        'max_value' => 20.00,
        'coefficient' => 1.00,
        'given_at' => '2024-01-15',
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/notes", $gradeData);

    $response->assertStatus(403);
    expect(Grade::count())->toBe(0);
});

test('teacher cannot create grade for subject they do not teach', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $gradeData = [
        'value' => 15.50,
        'max_value' => 20.00,
        'coefficient' => 1.00,
        'given_at' => '2024-01-15',
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/notes", $gradeData);

    $response->assertStatus(403);
    expect(Grade::count())->toBe(0);
});

test('student cannot create grades', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $gradeData = [
        'value' => 15.50,
        'max_value' => 20.00,
        'coefficient' => 1.00,
        'given_at' => '2024-01-15',
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($student)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/notes", $gradeData);

    $response->assertStatus(403);
    expect(Grade::count())->toBe(0);
});

test('grade creation validates required fields', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/notes", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value', 'max_value', 'given_at', 'academic_year']);

    expect(Grade::count())->toBe(0);
});
