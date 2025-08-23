<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Carbon\CarbonImmutable;

describe('Grade Model', function (): void {
    test('to array returns expected attributes', function (): void {
        $grade = Grade::factory()->create();

        $attributes = $grade->toArray();

        expect($attributes)->toHaveKeys([
            'id',
            'student_id',
            'teacher_id',
            'subject_id',
            'class_group_id',
            'value',
            'max_value',
            'coefficient',
            'title',
            'comment',
            'given_at',
            'academic_year',
            'created_at',
            'updated_at',
        ]);
    });

    test('casts decimal values correctly', function (): void {
        $grade = Grade::factory()->create([
            'value' => 15.50,
            'max_value' => 20.00,
            'coefficient' => 2.00,
        ]);

        expect($grade->value)->toBe('15.50');
        expect($grade->max_value)->toBe('20.00');
        expect($grade->coefficient)->toBe('2.00');
    });

    test('casts given_at to carbon date', function (): void {
        $date = '2024-01-15';
        $grade = Grade::factory()->create(['given_at' => $date]);

        expect($grade->given_at)->toBeInstanceOf(CarbonImmutable::class);
        expect($grade->given_at->toDateString())->toBe($date);
    });

    test('belongs to subject', function (): void {
        $subject = Subject::factory()->create();
        $grade = Grade::factory()->create(['subject_id' => $subject->id]);

        expect($grade->subject)->toBeInstanceOf(Subject::class);
        expect($grade->subject->id)->toBe($subject->id);
    });

    test('belongs to teacher user', function (): void {
        $teacher = User::factory()->create();
        $grade = Grade::factory()->create(['teacher_id' => $teacher->id]);

        expect($grade->teacher)->toBeInstanceOf(User::class);
        expect($grade->teacher->id)->toBe($teacher->id);
    });

    test('belongs to student user', function (): void {
        $student = User::factory()->create();
        $grade = Grade::factory()->create(['student_id' => $student->id]);

        expect($grade->student)->toBeInstanceOf(User::class);
        expect($grade->student->id)->toBe($student->id);
    });

    test('belongs to class group', function (): void {
        $classGroup = ClassGroup::factory()->create();
        $grade = Grade::factory()->create(['class_group_id' => $classGroup->id]);

        expect($grade->classGroup)->toBeInstanceOf(ClassGroup::class);
        expect($grade->classGroup->id)->toBe($classGroup->id);
    });

    test('uses uuid primary key', function (): void {
        $grade = Grade::factory()->create();

        expect($grade->id)->toBeString();
        expect(mb_strlen($grade->id))->toBe(36);
    });

    test('has factory', function (): void {
        $grade = Grade::factory()->create([
            'value' => 18.75,
            'title' => 'Examen Final',
            'comment' => 'Excellent travail',
        ]);

        expect($grade->value)->toBe('18.75');
        expect($grade->title)->toBe('Examen Final');
        expect($grade->comment)->toBe('Excellent travail');
    });

    test('can create grade with all relationships', function (): void {
        $student = User::factory()->create();
        $teacher = User::factory()->create();
        $subject = Subject::factory()->create();
        $classGroup = ClassGroup::factory()->create();

        $grade = Grade::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_group_id' => $classGroup->id,
            'value' => 16.50,
            'academic_year' => '2024-2025',
        ]);

        expect($grade->student->id)->toBe($student->id);
        expect($grade->teacher->id)->toBe($teacher->id);
        expect($grade->subject->id)->toBe($subject->id);
        expect($grade->classGroup->id)->toBe($classGroup->id);
        expect($grade->academic_year)->toBe('2024-2025');
    });

    test('handles nullable fields correctly', function (): void {
        $grade = Grade::factory()->create([
            'title' => null,
            'comment' => null,
        ]);

        expect($grade->title)->toBeNull();
        expect($grade->comment)->toBeNull();
    });

    test('stores academic year as string', function (): void {
        $grade = Grade::factory()->create(['academic_year' => '2024-2025']);

        expect($grade->academic_year)->toBe('2024-2025');
        expect($grade->academic_year)->toBeString();
    });
});
