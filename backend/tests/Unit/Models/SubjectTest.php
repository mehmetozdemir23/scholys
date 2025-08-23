<?php

declare(strict_types=1);

use App\Models\School;
use App\Models\Subject;
use App\Models\User;

describe('Subject Model', function (): void {
    test('to array returns expected attributes', function (): void {
        $subject = Subject::factory()->create();

        $attributes = $subject->toArray();

        expect($attributes)->toHaveKeys([
            'id',
            'school_id',
            'name',
            'created_at',
            'updated_at',
        ]);
    });

    test('belongs to school', function (): void {
        $school = School::factory()->create();
        $subject = Subject::factory()->create(['school_id' => $school->id]);

        expect($subject->school)->toBeInstanceOf(School::class);
        expect($subject->school->id)->toBe($school->id);
    });

    test('belongs to many teachers through pivot table', function (): void {
        $subject = Subject::factory()->create();
        $teacher1 = User::factory()->create();
        $teacher2 = User::factory()->create();

        $subject->teachers()->attach([$teacher1->id, $teacher2->id]);

        expect($subject->teachers)->toHaveCount(2);
        expect($subject->teachers->first())->toBeInstanceOf(User::class);
        expect($subject->teachers->pluck('id')->toArray())->toContain($teacher1->id, $teacher2->id);
    });

    test('can attach and detach teachers', function (): void {
        $subject = Subject::factory()->create();
        $teacher = User::factory()->create();

        $subject->teachers()->attach($teacher->id);
        $subject->refresh();
        expect($subject->teachers)->toHaveCount(1);

        $subject->teachers()->detach($teacher->id);
        $subject->refresh();
        expect($subject->teachers)->toHaveCount(0);
    });

    test('uses uuid primary key', function (): void {
        $subject = Subject::factory()->create();

        expect($subject->id)->toBeString();
        expect(mb_strlen($subject->id))->toBe(36);
    });

    test('has factory', function (): void {
        $subject = Subject::factory()->create([
            'name' => 'Mathématiques Avancées',
        ]);

        expect($subject->name)->toBe('Mathématiques Avancées');
        expect($subject->school_id)->not->toBeNull();
    });

    test('can create subject with specific school', function (): void {
        $school = School::factory()->create(['name' => 'École Test']);
        $subject = Subject::factory()->create([
            'school_id' => $school->id,
            'name' => 'Physique',
        ]);

        expect($subject->school->name)->toBe('École Test');
        expect($subject->name)->toBe('Physique');
    });
});
