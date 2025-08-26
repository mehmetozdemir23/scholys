<?php

declare(strict_types=1);

use App\Actions\SearchUsers;
use App\Models\Role;
use App\Models\School;
use App\Models\User;

describe('SearchUsers', function (): void {
    test('returns paginated users for given school', function (): void {
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();

        User::factory()->count(3)->create(['school_id' => $school1->id]);
        User::factory()->count(2)->create(['school_id' => $school2->id]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([], $school1->id);

        expect($result->total())->toBe(3)
            ->and($result->items())->toHaveCount(3);
    });

    test('filters users by search term in firstname', function (): void {
        $school = School::factory()->create();

        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Alice',
            'lastname' => 'Smith',
            'email' => 'alice@example.com',
        ]);
        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Bob',
            'lastname' => 'Johnson',
            'email' => 'bob@example.com',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['q' => 'Alice'], $school->id);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->firstname)->toBe('Alice');
    });

    test('filters users by search term in lastname', function (): void {
        $school = School::factory()->create();

        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
        ]);
        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['q' => 'Smith'], $school->id);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->lastname)->toBe('Smith');
    });

    test('filters users by search term in email', function (): void {
        $school = School::factory()->create();

        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Charlie',
            'lastname' => 'Brown',
            'email' => 'charlie@test.com',
        ]);
        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Diana',
            'lastname' => 'Prince',
            'email' => 'diana@example.com',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['q' => 'test.com'], $school->id);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->email)->toBe('charlie@test.com');
    });

    test('search is case insensitive', function (): void {
        $school = School::factory()->create();

        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Alice',
            'lastname' => 'Wonder',
            'email' => 'alice@example.com',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['q' => 'ALICE'], $school->id);

        expect($result->total())->toBe(1);
    });

    test('filters users by role', function (): void {
        $school = School::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $userRole = Role::factory()->create(['name' => 'user']);

        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $regularUser = User::factory()->create(['school_id' => $school->id]);

        $adminUser->roles()->attach($adminRole);
        $regularUser->roles()->attach($userRole);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['role' => 'admin'], $school->id);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->id)->toBe($adminUser->id);
    });

    test('sorts users by created_at desc by default', function (): void {
        $school = School::factory()->create();

        $oldUser = User::factory()->create([
            'school_id' => $school->id,
            'created_at' => now()->subDays(2),
        ]);
        $newUser = User::factory()->create([
            'school_id' => $school->id,
            'created_at' => now(),
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([], $school->id);

        expect($result->items()[0]->id)->toBe($newUser->id)
            ->and($result->items()[1]->id)->toBe($oldUser->id);
    });

    test('sorts users by firstname asc when specified', function (): void {
        $school = School::factory()->create();

        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Zoe',
        ]);
        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Alice',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([
            'sort_by' => 'firstname',
            'sort_order' => 'asc',
        ], $school->id);

        expect($result->items()[0]->firstname)->toBe('Alice')
            ->and($result->items()[1]->firstname)->toBe('Zoe');
    });

    test('respects per_page parameter', function (): void {
        $school = School::factory()->create();
        User::factory()->count(10)->create(['school_id' => $school->id]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['per_page' => 5], $school->id);

        expect($result->perPage())->toBe(5)
            ->and($result->items())->toHaveCount(5);
    });

    test('uses default per_page of 15 when not specified', function (): void {
        $school = School::factory()->create();
        User::factory()->count(20)->create(['school_id' => $school->id]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([], $school->id);

        expect($result->perPage())->toBe(15);
    });

    test('combines search and role filters', function (): void {
        $school = School::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'admin']);

        $adminAlice = User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Alice',
            'lastname' => 'Admin',
        ]);
        $userAlice = User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'Alice',
            'lastname' => 'User',
        ]);

        $adminAlice->roles()->attach($adminRole);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([
            'q' => 'Alice',
            'role' => 'admin',
        ], $school->id);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->id)->toBe($adminAlice->id);
    });

    test('returns empty result when no users match filters', function (): void {
        $school = School::factory()->create();
        User::factory()->create([
            'school_id' => $school->id,
            'firstname' => 'John',
        ]);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle(['q' => 'NonExistent'], $school->id);

        expect($result->total())->toBe(0)
            ->and($result->items())->toHaveCount(0);
    });

    test('loads user roles with users', function (): void {
        $school = School::factory()->create();
        $role = Role::factory()->create(['name' => 'test-role']);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->roles()->attach($role);

        $searchUsers = new SearchUsers();
        $result = $searchUsers->handle([], $school->id);

        expect($result->items()[0]->relationLoaded('roles'))->toBeTrue()
            ->and($result->items()[0]->roles)->toHaveCount(1)
            ->and($result->items()[0]->roles->first()->name)->toBe('test-role');
    });

    test('action handles instantiation', function (): void {
        $searchUsers = new SearchUsers();

        expect($searchUsers)->toBeInstanceOf(SearchUsers::class);
        expect(method_exists($searchUsers, 'handle'))->toBeTrue();
    });

    test('handle method accepts array and string parameters', function (): void {
        $school = School::factory()->create();
        $searchUsers = new SearchUsers();

        expect(function () use ($searchUsers, $school) {
            $searchUsers->handle([], $school->id);
        })->not->toThrow(Exception::class);
    });
});
