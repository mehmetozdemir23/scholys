<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;

require_once __DIR__ . '/../../../Helpers/TestHelpers.php';

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
        'title' => 'Contrôle Chapitre 1',
        'comment' => 'Bon travail',
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", $gradeData);

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
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", $gradeData);

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
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", $gradeData);

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
        'academic_year' => '2024-2025',
    ];

    $response = $this->actingAs($student)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", $gradeData);

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
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);

    expect(Grade::count())->toBe(0);
});

test('grade uses default values when not provided', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $teacher->subjects()->attach($subject);
    $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
    $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

    $gradeData = [
        'value' => 15.50,
    ];

    $response = $this->actingAs($teacher)
        ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades", $gradeData);

    $response->assertStatus(200);

    $grade = Grade::first();
    expect($grade->coefficient)->toBe('1.00')
        ->and($grade->max_value)->toBe('20.00')
        ->and($grade->is_active)->toBe(true)
        ->and($grade->given_at)->not->toBeNull();
});

test('teacher can update grade they created', function () {
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
        'value' => 15.50,
        'max_value' => 20.00,
        'coefficient' => 1.00,
    ]);

    $updateData = [
        'value' => 18.00,
        'title' => 'Contrôle corrigé',
        'comment' => 'Amélioration notable',
    ];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Note modifiée avec succès!']);

    $grade->refresh();
    expect($grade->value)->toBe('18.00')
        ->and($grade->title)->toBe('Contrôle corrigé')
        ->and($grade->comment)->toBe('Amélioration notable');
});

test('teacher cannot update grade created by another teacher', function () {
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
        'value' => 15.50,
    ]);

    $updateData = ['value' => 18.00];

    $response = $this->actingAs($teacher2)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(403);

    $grade->refresh();
    expect($grade->value)->toBe('15.50');
});

test('teacher cannot update grade if no longer assigned to subject', function () {
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
        'value' => 15.50,
    ]);


    $teacher->subjects()->detach($subject);

    $updateData = ['value' => 18.00];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(403);

    $grade->refresh();
    expect($grade->value)->toBe('15.50');
});

test('student cannot update grades', function () {
    $teacher = createTeacher();
    $student = createStudent();
    $subject = Subject::factory()->create(['school_id' => $teacher->school_id]);
    $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);

    $grade = Grade::factory()->create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_group_id' => $classGroup->id,
        'value' => 15.50,
    ]);

    $updateData = ['value' => 20.00];

    $response = $this->actingAs($student)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(403);

    $grade->refresh();
    expect($grade->value)->toBe('15.50');
});

test('grade update validates max_value constraint when both updated', function () {
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
        'value' => 15.50,
        'max_value' => 20.00,
    ]);

    $updateData = [
        'value' => 25.00,
        'max_value' => 20.00,
    ];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['max_value']);

    $grade->refresh();
    expect($grade->value)->toBe('15.50');
});

test('grade update validates max_value constraint when only value updated', function () {
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
        'value' => 15.50,
        'max_value' => 20.00,
    ]);


    $updateData = ['value' => 22.00];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(200);

    $grade->refresh();
    expect($grade->value)->toBe('22.00');
});

test('grade update allows equal value and max_value', function () {
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
        'value' => 15.50,
        'max_value' => 20.00,
    ]);

    $updateData = ['value' => 20.00];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(200);

    $grade->refresh();
    expect($grade->value)->toBe('20.00');
});

test('grade update validates when reducing max_value below current value', function () {
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
        'value' => 18.00,
        'max_value' => 20.00,
    ]);


    $updateData = ['max_value' => 15.00];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['max_value']);

    $grade->refresh();
    expect($grade->max_value)->toBe('20.00');
});

test('grade update validates academic year format', function () {
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
        'academic_year' => '2024-2025',
    ]);

    $updateData = ['academic_year' => 'invalid-year'];

    $response = $this->actingAs($teacher)
        ->patchJson("/api/class-groups/{$classGroup->id}/students/{$student->id}/subjects/{$subject->id}/grades/{$grade->id}", $updateData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['academic_year']);

    $grade->refresh();
    expect($grade->academic_year)->toBe('2024-2025');
});
