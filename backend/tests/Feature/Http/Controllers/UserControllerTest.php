<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('UserController', function (): void {
    describe('updatePassword', function (): void {
        test('updates user password successfully', function (): void {
            $user = User::factory()->create(['password' => bcrypt('old_password')]);

            $this->actingAs($user);
            $response = $this->postJson(route('user.password.update'), [
                'new_password' => 'new_password123',
                'new_password_confirmation' => 'new_password123',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Mot de passe mis à jour avec succès.']);

            $user->refresh();
            expect(Hash::check('new_password123', $user->password))->toBeTrue();
        });

        test('requires authentication', function (): void {
            $response = $this->postJson(route('user.password.update'), [
                'new_password' => 'new_password123',
                'new_password_confirmation' => 'new_password123',
            ]);

            $response->assertStatus(401);
        });

        test('validates new password is required', function (): void {
            $user = User::factory()->create();
            $this->actingAs($user);
            $response = $this->postJson(route('user.password.update'), []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
        });

        test('validates new password minimum length', function (): void {
            $user = User::factory()->create();
            $this->actingAs($user);
            $response = $this->postJson(route('user.password.update'), [
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
        });

        test('validates new password confirmation', function (): void {
            $user = User::factory()->create();
            $this->actingAs($user);
            $response = $this->postJson(route('user.password.update'), [
                'new_password' => 'valid_password123',
                'new_password_confirmation' => 'different_password',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
        });
    });

    describe('update', function (): void {
        test('updates user successfully with valid data', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
            ]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'email' => 'jane.smith@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Utilisateur modifié avec succès!']);

            $targetUser->refresh();
            expect($targetUser->firstname)->toBe('Jane')
                ->and($targetUser->lastname)->toBe('Smith')
                ->and($targetUser->email)->toBe('jane.smith@example.com');
        });

        test('allows partial updates', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
            ]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'firstname' => 'Jane',
            ]);

            $response->assertStatus(200);

            $targetUser->refresh();
            expect($targetUser->firstname)->toBe('Jane')
                ->and($targetUser->lastname)->toBe('Doe')
                ->and($targetUser->email)->toBe('john.doe@example.com');
        });

        test('requires authentication', function (): void {
            $user = User::factory()->create();

            $response = $this->patchJson(route('users.update', $user), [
                'firstname' => 'Jane',
            ]);

            $response->assertStatus(401);
        });

        test('requires super admin role', function (): void {
            $school = School::factory()->create();
            $regularUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);

            $this->actingAs($regularUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'firstname' => 'Jane',
            ]);

            $response->assertStatus(403);
        });

        test('validates firstname when provided', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'firstname' => '',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['firstname']);
        });

        test('validates firstname maximum length', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'firstname' => str_repeat('a', 256),
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['firstname']);
        });

        test('validates lastname when provided', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'lastname' => '',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['lastname']);
        });

        test('validates email format when provided', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('validates email uniqueness when provided', function (): void {
            $role = Role::create(['name' => Role::SUPER_ADMIN]);
            $school = School::factory()->create();
            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $existingUser = User::factory()->create([
                'school_id' => $school->id,
                'email' => 'existing@example.com',
            ]);
            $adminUser->roles()->attach($role);

            $this->actingAs($adminUser);
            $response = $this->patchJson(route('users.update', $targetUser), [
                'email' => 'existing@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('search', function (): void {
        test('returns paginated users for authenticated user school', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            User::factory()->count(3)->create(['school_id' => $school->id]);
            User::factory()->count(2)->create();

            $this->actingAs($user);
            $response = $this->getJson(route('users.search'));

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'firstname', 'lastname', 'email', 'roles'],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                ]);

            expect($response->json('total'))->toBe(4);
        });

        test('filters users by search term', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'Alice',
                'lastname' => 'Wonder',
                'email' => 'alice@example.com',
            ]);
            User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'Bob',
                'lastname' => 'Builder',
                'email' => 'bob@example.com',
            ]);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', ['q' => 'Alice']));

            $response->assertStatus(200);
            expect($response->json('total'))->toBe(1)
                ->and($response->json('data.0.firstname'))->toBe('Alice');
        });

        test('filters users by role', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $adminRole = Role::factory()->create(['name' => 'admin']);

            $adminUser = User::factory()->create(['school_id' => $school->id]);
            $regularUser = User::factory()->create(['school_id' => $school->id]);

            $adminUser->roles()->attach($adminRole);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', ['role' => 'admin']));

            $response->assertStatus(200);
            expect($response->json('total'))->toBe(1);
        });

        test('sorts users by specified field and order', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'Zoe',
            ]);
            User::factory()->create([
                'school_id' => $school->id,
                'firstname' => 'Alice',
            ]);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', [
                'sort_by' => 'firstname',
                'sort_order' => 'asc',
            ]));

            $response->assertStatus(200);
            $users = $response->json('data');
            expect($users[0]['firstname'])->toBe('Alice');
        });

        test('respects pagination parameters', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            User::factory()->count(10)->create(['school_id' => $school->id]);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', [
                'per_page' => 5,
                'page' => 1,
            ]));

            $response->assertStatus(200);
            expect($response->json('per_page'))->toBe(5)
                ->and($response->json('current_page'))->toBe(1)
                ->and($response->json('data'))->toHaveCount(5);
        });

        test('combines multiple filters', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
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

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', [
                'q' => 'Alice',
                'role' => 'admin',
            ]));

            $response->assertStatus(200);
            expect($response->json('total'))->toBe(1)
                ->and($response->json('data.0.firstname'))->toBe('Alice')
                ->and($response->json('data.0.lastname'))->toBe('Admin');
        });

        test('returns empty results when no matches found', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', ['q' => 'NonExistent']));

            $response->assertStatus(200);
            expect($response->json('total'))->toBe(0)
                ->and($response->json('data'))->toHaveCount(0);
        });

        test('only returns users from same school', function (): void {
            $school1 = School::factory()->create();
            $school2 = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school1->id]);

            User::factory()->count(2)->create(['school_id' => $school1->id]);
            User::factory()->count(3)->create(['school_id' => $school2->id]);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search'));

            $response->assertStatus(200);
            expect($response->json('total'))->toBe(3);
        });

        test('requires authentication', function (): void {
            $response = $this->getJson(route('users.search'));

            $response->assertStatus(401);
        });

        test('validates search parameters', function (): void {
            $user = User::factory()->create();

            $this->actingAs($user);
            $response = $this->getJson(route('users.search', [
                'sort_by' => 'invalid_field',
                'sort_order' => 'invalid_order',
                'per_page' => 101,
            ]));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['sort_by', 'sort_order', 'per_page']);
        });

        test('includes user roles in response', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $role = Role::factory()->create(['name' => 'test-role']);

            $targetUser = User::factory()->create(['school_id' => $school->id]);
            $targetUser->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->getJson(route('users.search'));

            $response->assertStatus(200);
            $userData = collect($response->json('data'))
                ->firstWhere('id', $targetUser->id);

            expect($userData['roles'])->toHaveCount(1)
                ->and($userData['roles'][0]['name'])->toBe('test-role');
        });
    });
});
