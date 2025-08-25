<?php

declare(strict_types=1);

use App\Actions\UpdateGrade;
use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('UpdateGrade updates grade attributes', function (): void {
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
        'title' => 'Original title',
    ]);

    $updateAction = new UpdateGrade();
    
    $updateAction->handle($grade, [
        'value' => 18.00,
        'title' => 'Updated title',
        'comment' => 'Improved performance',
    ]);

    $grade->refresh();
    expect($grade->value)->toBe('18.00')
        ->and($grade->title)->toBe('Updated title')
        ->and($grade->comment)->toBe('Improved performance');
});

test('UpdateGrade logs the modification', function (): void {
    Log::spy();

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

    $updateAction = new UpdateGrade();
    
    $updateAction->handle($grade, ['value' => 18.00]);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Grade updated', Mockery::on(function ($data) use ($grade) {
            return $data['grade_id'] === $grade->id
                && $data['teacher_id'] === $grade->teacher_id
                && $data['student_id'] === $grade->student_id
                && $data['subject_id'] === $grade->subject_id
                && isset($data['updated_attributes'])
                && isset($data['modified_at']);
        }));
});