<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Carbon\CarbonImmutable;

describe('User Model', function (): void {
    test('to array returns expected attributes', function (): void {
        $user = User::factory()->create();

        $attributes = $user->toArray();

        expect($attributes)->toHaveKeys([
            'id',
            'firstname',
            'lastname',
            'school_id',
            'email',
        ]);
    });

    test('hides sensitive attributes from serialization', function (): void {
        $user = User::factory()->create(['password' => 'secret123']);

        $attributes = $user->toArray();

        expect($attributes)->not->toHaveKey('password')
            ->and($attributes)->not->toHaveKey('remember_token');
    });

    test('casts email_verified_at to datetime', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        expect($user->email_verified_at)->toBeInstanceOf(CarbonImmutable::class);
    });

    test('casts password to hashed value', function (): void {
        $user = User::factory()->create(['password' => 'plaintext']);

        expect($user->password)->not->toBe('plaintext')
            ->and(mb_strlen($user->password))->toBeGreaterThan(50);
    });

    describe('relationships', function () {
        test('belongs to a school', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            expect($user->school)->toBeInstanceOf(School::class)
                ->and($user->school->id)->toBe($school->id);
        });

        test('school has many users', function (): void {
            $school = School::factory()->create();
            $user1 = User::factory()->create(['school_id' => $school->id]);
            $user2 = User::factory()->create(['school_id' => $school->id]);

            expect($school->users)->toHaveCount(2)
                ->and($school->users->pluck('id'))->toContain($user1->id, $user2->id);
        });
    });

    describe('roles relationship', function () {
        test('has many roles', function (): void {
            $user = User::factory()->create();
            $role = Role::factory()->create();
            $user->roles()->attach($role);

            expect($user->roles)->toHaveCount(1)
                ->and($user->roles->first())->toBeInstanceOf(Role::class)
                ->and($user->roles->first()->id)->toBe($role->id);
        });
    });

    describe('classGroups relationship', function () {
        test('has many class groups', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $classGroup1 = ClassGroup::factory()->create(['school_id' => $school->id]);
            $classGroup2 = ClassGroup::factory()->create(['school_id' => $school->id]);

            $user->classGroups()->attach([$classGroup1->id, $classGroup2->id], ['assigned_at' => now()]);

            expect($user->classGroups)->toHaveCount(2)
                ->and($user->classGroups->first())->toBeInstanceOf(ClassGroup::class);
        });

        test('class groups relationship includes pivot data', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $user->classGroups()->attach($classGroup->id, ['assigned_at' => now()]);

            $pivotData = $user->classGroups->first()->pivot;
            expect($pivotData->assigned_at)->not->toBeNull();
        });

        test('class groups relationship uses correct pivot table', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $user->classGroups()->attach($classGroup->id, ['assigned_at' => now()]);

            $this->assertDatabaseHas('class_group_user', [
                'user_id' => $user->id,
                'class_group_id' => $classGroup->id,
            ]);
        });
    });

    describe('assignRole method', function () {
        test('assigns existing role to user', function (): void {
            $user = User::factory()->create();
            Role::create(['name' => 'super_admin']);

            $user->assignRole('super_admin');

            expect($user->roles->pluck('name'))->toContain('super_admin');
        });

        test('throws exception for non-existent role', function (): void {
            $user = User::factory()->create();

            expect(fn () => $user->assignRole('non_existent_role'))
                ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });

        test('does not create duplicates when assigning same role twice', function (): void {
            $user = User::factory()->create();
            Role::create(['name' => 'super_admin']);

            $user->assignRole('super_admin');
            $user->assignRole('super_admin');

            expect($user->roles)->toHaveCount(1);
        });
    });

    describe('hasRole method', function () {
        test('returns true for assigned role', function (): void {
            $user = User::factory()->create();
            Role::create(['name' => 'super_admin']);
            $user->assignRole('super_admin');

            expect($user->hasRole('super_admin'))->toBeTrue();
        });

        test('returns false for unassigned role', function (): void {
            $user = User::factory()->create();
            Role::create(['name' => 'super_admin']);

            expect($user->hasRole('super_admin'))->toBeFalse();
        });

        test('returns false for non-existent role', function (): void {
            $user = User::factory()->create();

            expect($user->hasRole('non_existent_role'))->toBeFalse();
        });
    });

    test('uses UUIDs for primary key', function (): void {
        $user = User::factory()->create();

        expect($user->id)->toBeString()
            ->and(mb_strlen($user->id))->toBe(36)
            ->and($user->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });
});
