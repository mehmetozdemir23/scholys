<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\School;
use App\Models\User;
use App\Policies\ClassGroupPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

uses(RefreshDatabase::class);

describe('ClassGroupPolicy', function (): void {
    describe('viewAny', function (): void {
        test('super admin can view any class groups', function (): void {
            $user = createUserWithRole('super_admin');
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($user))->toBeTrue();
        });

        test('admin can view any class groups', function (): void {
            $user = createUserWithRole('admin');
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($user))->toBeTrue();
        });

        test('teacher can view any class groups', function (): void {
            $user = createUserWithRole('teacher');
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($user))->toBeTrue();
        });

        test('student cannot view any class groups', function (): void {
            $user = createUserWithRole('student');
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($user))->toBeFalse();
        });

        test('user with no role cannot view any class groups', function (): void {
            $user = User::factory()->create();
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($user))->toBeFalse();
        });
    });

    describe('view', function (): void {
        test('super admin can view class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('super_admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeTrue();
        });

        test('super admin cannot view class group from different school', function (): void {
            $user = createUserWithRole('super_admin');
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $otherSchool->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeFalse();
        });

        test('admin can view class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeTrue();
        });

        test('teacher can view class group they are assigned to', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $classGroup->teachers()->attach($user->id, ['assigned_at' => now()]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeTrue();
        });

        test('teacher cannot view class group they are not assigned to', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeFalse();
        });

        test('student can view class group they are assigned to', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('student', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $classGroup->students()->attach($user->id, ['assigned_at' => now()]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeTrue();
        });

        test('student cannot view class group they are not assigned to', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('student', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeFalse();
        });

        test('user with no role cannot view class group', function (): void {
            $user = User::factory()->create();
            $classGroup = ClassGroup::factory()->create();
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeFalse();
        });
    });

    describe('create', function (): void {
        test('super admin can create class groups', function (): void {
            $user = createUserWithRole('super_admin');
            $policy = new ClassGroupPolicy();

            expect($policy->create($user))->toBeTrue();
        });

        test('admin can create class groups', function (): void {
            $user = createUserWithRole('admin');
            $policy = new ClassGroupPolicy();

            expect($policy->create($user))->toBeTrue();
        });

        test('teacher cannot create class groups', function (): void {
            $user = createUserWithRole('teacher');
            $policy = new ClassGroupPolicy();

            expect($policy->create($user))->toBeFalse();
        });

        test('student cannot create class groups', function (): void {
            $user = createUserWithRole('student');
            $policy = new ClassGroupPolicy();

            expect($policy->create($user))->toBeFalse();
        });
    });

    describe('update', function (): void {
        test('super admin can update class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('super_admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($user, $classGroup))->toBeTrue();
        });

        test('admin can update class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($user, $classGroup))->toBeTrue();
        });

        test('admin cannot update class group from different school', function (): void {
            $user = createUserWithRole('admin');
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $otherSchool->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($user, $classGroup))->toBeFalse();
        });

        test('teacher cannot update class group', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($user, $classGroup))->toBeFalse();
        });
    });

    describe('delete', function (): void {
        test('super admin can delete class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('super_admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($user, $classGroup))->toBeTrue();
        });

        test('admin can delete class group from same school', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($user, $classGroup))->toBeTrue();
        });

        test('teacher cannot delete class group', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($user, $classGroup))->toBeFalse();
        });
    });

    describe('assignStudent', function (): void {
        test('super admin can assign students', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('super_admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($user, $classGroup))->toBeTrue();
        });

        test('admin can assign students', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($user, $classGroup))->toBeTrue();
        });

        test('teacher cannot assign students', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($user, $classGroup))->toBeFalse();
        });
    });

    describe('assignTeacher', function (): void {
        test('super admin can assign teachers', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('super_admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($user, $classGroup))->toBeTrue();
        });

        test('admin can assign teachers', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('admin', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($user, $classGroup))->toBeTrue();
        });

        test('teacher cannot assign teachers', function (): void {
            $school = School::factory()->create();
            $user = createUserWithRole('teacher', $school->id);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($user, $classGroup))->toBeFalse();
        });
    });

    describe('viewStats', function (): void {
        test('super admin can view stats', function (): void {
            $user = createUserWithRole('super_admin');
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($user))->toBeTrue();
        });

        test('admin can view stats', function (): void {
            $user = createUserWithRole('admin');
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($user))->toBeTrue();
        });

        test('teacher cannot view stats', function (): void {
            $user = createUserWithRole('teacher');
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($user))->toBeFalse();
        });

        test('student cannot view stats', function (): void {
            $user = createUserWithRole('student');
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($user))->toBeFalse();
        });
    });
});
