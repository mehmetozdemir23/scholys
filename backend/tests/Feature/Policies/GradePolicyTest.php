<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\School;
use App\Models\Subject;
use App\Policies\GradePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

uses(RefreshDatabase::class);

describe('GradePolicy', function (): void {
    describe('create', function (): void {
        test('teacher can create grade when teaches subject and student is in class', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $teacher->subjects()->attach($subject);
            $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);
            $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeTrue();
        });

        test('teacher cannot create grade when does not teach subject', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeFalse();
        });

        test('teacher cannot create grade when student is not in class', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $teacher->subjects()->attach($subject);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeFalse();
        });

        test('non-teacher cannot create grades', function (): void {
            $school = School::factory()->create();
            $admin = createUserWithRole('admin', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $policy = new GradePolicy();

            expect($policy->create($admin, $classGroup, $student, $subject))->toBeFalse();
        });

        test('student cannot create grades', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $policy = new GradePolicy();

            expect($policy->create($student, $classGroup, $teacher, $subject))->toBeFalse();
        });

        test('teacher cannot create grade for non-student user', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $admin = createUserWithRole('admin', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $teacher->subjects()->attach($subject);
            $admin->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup, $admin, $subject))->toBeFalse();
        });

        test('policy works with multiple subjects and classes', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject1 = Subject::factory()->create(['school_id' => $school->id]);
            $subject2 = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup1 = ClassGroup::factory()->create(['school_id' => $school->id]);
            $classGroup2 = ClassGroup::factory()->create(['school_id' => $school->id]);

            $teacher->subjects()->attach([$subject1->id, $subject2->id]);
            $teacher->classGroups()->attach([$classGroup1->id, $classGroup2->id], ['assigned_at' => '2024-09-01']);

            $student->classGroups()->attach([$classGroup1->id, $classGroup2->id], ['assigned_at' => '2024-09-01']);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup1, $student, $subject1))->toBeTrue();
            expect($policy->create($teacher, $classGroup2, $student, $subject2))->toBeTrue();
            expect($policy->create($teacher, $classGroup1, $student, $subject2))->toBeTrue();
            expect($policy->create($teacher, $classGroup2, $student, $subject1))->toBeTrue();
        });

        test('policy validates relationships correctly with pivot tables', function (): void {
            $school = School::factory()->create();
            $teacher = createUserWithRole('teacher', $school->id);
            $student = createUserWithRole('student', $school->id);
            $subject = Subject::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $teacher->subjects()->attach($subject);
            $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new GradePolicy();

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeTrue();

            $teacher->subjects()->detach($subject);

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeFalse();

            $teacher->subjects()->attach($subject);
            $student->classGroups()->detach($classGroup);

            expect($policy->create($teacher, $classGroup, $student, $subject))->toBeFalse();
        });
    });
});
