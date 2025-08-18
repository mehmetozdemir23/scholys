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
});
