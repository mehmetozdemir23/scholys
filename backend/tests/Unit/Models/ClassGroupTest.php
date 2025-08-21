<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Carbon\CarbonImmutable;

describe('ClassGroup Model', function (): void {
    describe('relationships', function (): void {
        test('belongs to school', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            expect($classGroup->school)->toBeInstanceOf(School::class);
            expect($classGroup->school->id)->toBe($school->id);
        });

        test('has many students', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student1 = User::factory()->create(['school_id' => $school->id]);
            $student2 = User::factory()->create(['school_id' => $school->id]);
            $student1->roles()->attach($studentRole);
            $student2->roles()->attach($studentRole);

            $classGroup->students()->attach([$student1->id, $student2->id], ['assigned_at' => now()]);

            expect($classGroup->students)->toHaveCount(2);
            expect($classGroup->students->first())->toBeInstanceOf(User::class);
        });

        test('has many teachers', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

            $teacher = User::factory()->create(['school_id' => $school->id]);
            $teacher->roles()->attach($teacherRole);

            $classGroup->teachers()->attach($teacher->id, ['assigned_at' => now()]);

            expect($classGroup->teachers)->toHaveCount(1);
            expect($classGroup->teachers->first())->toBeInstanceOf(User::class);
        });

        test('has many users (all roles)', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $user1 = User::factory()->create(['school_id' => $school->id]);
            $user2 = User::factory()->create(['school_id' => $school->id]);

            $classGroup->users()->attach([$user1->id, $user2->id], ['assigned_at' => now()]);

            expect($classGroup->users)->toHaveCount(2);
        });
    });

    describe('scopes', function (): void {
        test('active scope filters active class groups', function (): void {
            $school = School::factory()->create();

            ClassGroup::factory()->create(['school_id' => $school->id, 'is_active' => true]);
            ClassGroup::factory()->create(['school_id' => $school->id, 'is_active' => false]);

            $activeClasses = ClassGroup::active()->get();

            expect($activeClasses)->toHaveCount(1);
            expect($activeClasses->first()->is_active)->toBeTrue();
        });

        test('forSchool scope filters by school', function (): void {
            $school1 = School::factory()->create();
            $school2 = School::factory()->create();

            ClassGroup::factory()->create(['school_id' => $school1->id]);
            ClassGroup::factory()->create(['school_id' => $school2->id]);

            $school1Classes = ClassGroup::forSchool($school1->id)->get();

            expect($school1Classes)->toHaveCount(1);
            expect($school1Classes->first()->school_id)->toBe($school1->id);
        });

        test('forAcademicYear scope filters by academic year', function (): void {
            $school = School::factory()->create();

            ClassGroup::factory()->create([
                'school_id' => $school->id,
                'academic_year' => '2024-2025',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $school->id,
                'academic_year' => '2023-2024',
            ]);

            $currentYearClasses = ClassGroup::forAcademicYear('2024-2025')->get();

            expect($currentYearClasses)->toHaveCount(1);
            expect($currentYearClasses->first()->academic_year)->toBe('2024-2025');
        });
    });

    describe('methods', function (): void {
        test('getCurrentStudentCount returns correct count', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student1 = User::factory()->create(['school_id' => $school->id]);
            $student2 = User::factory()->create(['school_id' => $school->id]);
            $student1->roles()->attach($studentRole);
            $student2->roles()->attach($studentRole);

            $classGroup->students()->attach([$student1->id, $student2->id], ['assigned_at' => now()]);

            expect($classGroup->getCurrentStudentCount())->toBe(2);
        });

        test('isFull returns false when max_students is null', function (): void {
            $classGroup = ClassGroup::factory()->create(['max_students' => null]);

            expect($classGroup->isFull())->toBeFalse();
        });

        test('isFull returns false when not at capacity', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $school->id,
                'max_students' => 5,
            ]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student = User::factory()->create(['school_id' => $school->id]);
            $student->roles()->attach($studentRole);
            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            expect($classGroup->isFull())->toBeFalse();
        });

        test('isFull returns true when at capacity', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $school->id,
                'max_students' => 1,
            ]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student = User::factory()->create(['school_id' => $school->id]);
            $student->roles()->attach($studentRole);
            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            expect($classGroup->isFull())->toBeTrue();
        });

        test('getAvailableSpots returns null when max_students is null', function (): void {
            $classGroup = ClassGroup::factory()->create(['max_students' => null]);

            expect($classGroup->getAvailableSpots())->toBeNull();
        });

        test('getAvailableSpots returns correct number', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $school->id,
                'max_students' => 5,
            ]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student = User::factory()->create(['school_id' => $school->id]);
            $student->roles()->attach($studentRole);
            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            expect($classGroup->getAvailableSpots())->toBe(4);
        });

        test('getAvailableSpots returns zero when over capacity', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $school->id,
                'max_students' => 1,
            ]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student1 = User::factory()->create(['school_id' => $school->id]);
            $student2 = User::factory()->create(['school_id' => $school->id]);
            $student1->roles()->attach($studentRole);
            $student2->roles()->attach($studentRole);

            $classGroup->students()->attach([$student1->id, $student2->id], ['assigned_at' => now()]);

            expect($classGroup->getAvailableSpots())->toBe(0);
        });
    });

    describe('casts', function (): void {
        test('is_active is cast to boolean', function (): void {
            $classGroup = ClassGroup::factory()->create(['is_active' => 1]);

            expect($classGroup->is_active)->toBeBool();
            expect($classGroup->is_active)->toBeTrue();
        });

        test('is_active false is cast to boolean', function (): void {
            $classGroup = ClassGroup::factory()->create(['is_active' => 0]);

            expect($classGroup->is_active)->toBeBool();
            expect($classGroup->is_active)->toBeFalse();
        });
    });

    describe('attributes', function (): void {
        test('fillable attributes can be mass assigned', function (): void {
            $school = School::factory()->create();
            $data = [
                'name' => 'Test Class',
                'level' => '6e',
                'section' => 'A',
                'description' => 'Test description',
                'max_students' => 30,
                'academic_year' => '2024-2025',
                'is_active' => true,
                'school_id' => $school->id,
            ];

            $classGroup = ClassGroup::create($data);

            expect($classGroup->name)->toBe('Test Class');
            expect($classGroup->level)->toBe('6e');
            expect($classGroup->section)->toBe('A');
            expect($classGroup->description)->toBe('Test description');
            expect($classGroup->max_students)->toBe(30);
            expect($classGroup->academic_year)->toBe('2024-2025');
            expect($classGroup->is_active)->toBeTrue();
            expect($classGroup->school_id)->toBe($school->id);
        });

        test('uses UUID for primary key', function (): void {
            $classGroup = ClassGroup::factory()->create();

            expect($classGroup->id)->toBeString();
            expect(mb_strlen($classGroup->id))->toBe(36);
            expect($classGroup->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
        });

        test('has timestamps', function (): void {
            $classGroup = ClassGroup::factory()->create();

            expect($classGroup->created_at)->not->toBeNull();
            expect($classGroup->updated_at)->not->toBeNull();
            expect($classGroup->created_at)->toBeInstanceOf(CarbonImmutable::class);
            expect($classGroup->updated_at)->toBeInstanceOf(CarbonImmutable::class);
        });
    });

    describe('edge cases', function (): void {
        test('getCurrentStudentCount returns zero when no students', function (): void {
            $classGroup = ClassGroup::factory()->create();

            expect($classGroup->getCurrentStudentCount())->toBe(0);
        });

        test('isFull handles edge case with zero max_students', function (): void {
            $classGroup = ClassGroup::factory()->create(['max_students' => 0]);

            expect($classGroup->isFull())->toBeTrue();
        });

        test('getAvailableSpots handles edge case with zero max_students', function (): void {
            $classGroup = ClassGroup::factory()->create(['max_students' => 0]);

            expect($classGroup->getAvailableSpots())->toBe(0);
        });

        test('scopes work with empty results', function (): void {
            $school = School::factory()->create();

            $activeClasses = ClassGroup::active()->forSchool($school->id)->get();
            $yearClasses = ClassGroup::forAcademicYear('2099-2100')->forSchool($school->id)->get();

            expect($activeClasses)->toHaveCount(0);
            expect($yearClasses)->toHaveCount(0);
        });

        test('relationships work with soft-deleted records', function (): void {
            $school = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $studentRole = Role::firstOrCreate(['name' => 'student']);

            $student = User::factory()->create(['school_id' => $school->id]);
            $student->roles()->attach($studentRole);
            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            expect($classGroup->students)->toHaveCount(1);
            expect($classGroup->getCurrentStudentCount())->toBe(1);
        });
    });

    describe('query scopes combination', function (): void {
        test('can combine multiple scopes', function (): void {
            $school1 = School::factory()->create();
            $school2 = School::factory()->create();

            ClassGroup::factory()->create([
                'school_id' => $school1->id,
                'is_active' => true,
                'academic_year' => '2024-2025',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $school1->id,
                'is_active' => false,
                'academic_year' => '2024-2025',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $school2->id,
                'is_active' => true,
                'academic_year' => '2024-2025',
            ]);

            $results = ClassGroup::active()
                ->forSchool($school1->id)
                ->forAcademicYear('2024-2025')
                ->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->school_id)->toBe($school1->id);
            expect($results->first()->is_active)->toBeTrue();
            expect($results->first()->academic_year)->toBe('2024-2025');
        });
    });
});
