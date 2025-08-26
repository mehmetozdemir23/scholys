<?php

declare(strict_types=1);

use App\Actions\DeactivateGrade;
use App\Models\Grade;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

test('deactivates grade and sets deactivated_at datetime', function (): void {
    $teacher = createTeacher();
    $student = createStudent();

    $grade = Grade::factory()->create([
        'teacher_id' => $teacher->id,
        'student_id' => $student->id,
        'is_active' => true,
        'deactivated_at' => null,
    ]);

    $deactivateAction = new DeactivateGrade();
    $deactivateAction->handle($grade);

    $grade->refresh();
    expect($grade->is_active)->toBeFalse()
        ->and($grade->deactivated_at)->not->toBeNull()
        ->and($grade->deactivated_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('logs grade deactivation with correct data', function (): void {
    Log::spy();

    $teacher = createTeacher();
    $student = createStudent();

    $grade = Grade::factory()->create([
        'teacher_id' => $teacher->id,
        'student_id' => $student->id,
        'is_active' => true,
    ]);

    $deactivateAction = new DeactivateGrade();
    $deactivateAction->handle($grade);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Grade deactivated', Mockery::on(function ($data) use ($grade) {
            return $data['grade_id'] === $grade->id
                && $data['teacher_id'] === $grade->teacher_id
                && $data['student_id'] === $grade->student_id
                && $data['subject_id'] === $grade->subject_id
                && isset($data['deactivated_at']);
        }));
});
