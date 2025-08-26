<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\User;
use App\Policies\ClassGroupPolicy;

require_once __DIR__.'/../../Helpers/TestHelpers.php';

describe('ClassGroupPolicy', function (): void {

    describe('viewAny', function (): void {
        test('super admin can view any class groups', function (): void {
            $superAdmin = createSuperAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($superAdmin))->toBeTrue();
        });

        test('admin can view any class groups', function (): void {
            $admin = createAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($admin))->toBeTrue();
        });

        test('teacher can view any class groups', function (): void {
            $teacher = createTeacher();
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($teacher))->toBeTrue();
        });

        test('student cannot view any class groups', function (): void {
            $student = createStudent();
            $policy = new ClassGroupPolicy();

            expect($policy->viewAny($student))->toBeFalse();
        });
    });

    describe('view', function (): void {
        test('super admin can view class group in same school', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $superAdmin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($superAdmin, $classGroup))->toBeTrue();
        });

        test('super admin cannot view class group in different school', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create();
            $policy = new ClassGroupPolicy();

            expect($policy->view($superAdmin, $classGroup))->toBeFalse();
        });

        test('admin can view class group in same school', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($admin, $classGroup))->toBeTrue();
        });

        test('teacher can view class group they are assigned to', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new ClassGroupPolicy();

            expect($policy->view($teacher, $classGroup))->toBeTrue();
        });

        test('teacher cannot view class group they are not assigned to', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($teacher, $classGroup))->toBeFalse();
        });

        test('student can view class group they are assigned to', function (): void {
            $student = createStudent();
            $classGroup = ClassGroup::factory()->create(['school_id' => $student->school_id]);
            $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new ClassGroupPolicy();

            expect($policy->view($student, $classGroup))->toBeTrue();
        });

        test('student cannot view class group they are not assigned to', function (): void {
            $student = createStudent();
            $classGroup = ClassGroup::factory()->create(['school_id' => $student->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($student, $classGroup))->toBeFalse();
        });

        test('student cannot view class group in different school', function (): void {
            $student = createStudent();
            $classGroup = ClassGroup::factory()->create();
            $student->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new ClassGroupPolicy();

            expect($policy->view($student, $classGroup))->toBeFalse();
        });

        test('teacher cannot view class group in different school', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create();
            $teacher->classGroups()->attach($classGroup, ['assigned_at' => '2024-09-01']);

            $policy = new ClassGroupPolicy();

            expect($policy->view($teacher, $classGroup))->toBeFalse();
        });

        test('user with unknown role cannot view class group', function (): void {
            $user = User::factory()->create();

            $classGroup = ClassGroup::factory()->create(['school_id' => $user->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->view($user, $classGroup))->toBeFalse();
        });
    });

    describe('create', function (): void {
        test('super admin can create class groups', function (): void {
            $superAdmin = createSuperAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->create($superAdmin))->toBeTrue();
        });

        test('admin can create class groups', function (): void {
            $admin = createAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->create($admin))->toBeTrue();
        });

        test('teacher cannot create class groups', function (): void {
            $teacher = createTeacher();
            $policy = new ClassGroupPolicy();

            expect($policy->create($teacher))->toBeFalse();
        });

        test('student cannot create class groups', function (): void {
            $student = createStudent();
            $policy = new ClassGroupPolicy();

            expect($policy->create($student))->toBeFalse();
        });
    });

    describe('update', function (): void {
        test('super admin can update class group in same school', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $superAdmin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($superAdmin, $classGroup))->toBeTrue();
        });

        test('admin can update class group in same school', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($admin, $classGroup))->toBeTrue();
        });

        test('admin cannot update class group in different school', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create();
            $policy = new ClassGroupPolicy();

            expect($policy->update($admin, $classGroup))->toBeFalse();
        });

        test('teacher cannot update class groups', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->update($teacher, $classGroup))->toBeFalse();
        });
    });

    describe('delete', function (): void {
        test('super admin can delete class group in same school', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $superAdmin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($superAdmin, $classGroup))->toBeTrue();
        });

        test('admin can delete class group in same school', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($admin, $classGroup))->toBeTrue();
        });

        test('teacher cannot delete class groups', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->delete($teacher, $classGroup))->toBeFalse();
        });
    });

    describe('assignStudent', function (): void {
        test('super admin can assign students to class group', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $superAdmin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($superAdmin, $classGroup))->toBeTrue();
        });

        test('admin can assign students to class group', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($admin, $classGroup))->toBeTrue();
        });

        test('teacher cannot assign students to class group', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignStudent($teacher, $classGroup))->toBeFalse();
        });
    });

    describe('assignTeacher', function (): void {
        test('super admin can assign teachers to class group', function (): void {
            $superAdmin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $superAdmin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($superAdmin, $classGroup))->toBeTrue();
        });

        test('admin can assign teachers to class group', function (): void {
            $admin = createAdmin();
            $classGroup = ClassGroup::factory()->create(['school_id' => $admin->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($admin, $classGroup))->toBeTrue();
        });

        test('teacher cannot assign teachers to class group', function (): void {
            $teacher = createTeacher();
            $classGroup = ClassGroup::factory()->create(['school_id' => $teacher->school_id]);
            $policy = new ClassGroupPolicy();

            expect($policy->assignTeacher($teacher, $classGroup))->toBeFalse();
        });
    });

    describe('viewStats', function (): void {
        test('super admin can view class group stats', function (): void {
            $superAdmin = createSuperAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($superAdmin))->toBeTrue();
        });

        test('admin can view class group stats', function (): void {
            $admin = createAdmin();
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($admin))->toBeTrue();
        });

        test('teacher cannot view class group stats', function (): void {
            $teacher = createTeacher();
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($teacher))->toBeFalse();
        });

        test('student cannot view class group stats', function (): void {
            $student = createStudent();
            $policy = new ClassGroupPolicy();

            expect($policy->viewStats($student))->toBeFalse();
        });
    });
});
