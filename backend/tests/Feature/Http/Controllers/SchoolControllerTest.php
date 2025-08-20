<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\School;
use App\Models\User;

describe('SchoolController', function (): void {
    describe('update', function (): void {
        test('updates school successfully with valid data', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create([
                'name' => 'Old School Name',
                'address' => 'Old Address',
            ]);
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'New School Name',
                'address' => 'New Address',
                'contact_email' => 'new@school.com',
                'contact_phone' => '1234567890',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'École mise à jour avec succès.']);

            $school->refresh();
            expect($school->name)->toBe('New School Name')
                ->and($school->address)->toBe('New Address')
                ->and($school->contact_email)->toBe('new@school.com')
                ->and($school->contact_phone)->toBe('1234567890');
        });

        test('requires authentication', function (): void {
            $school = School::factory()->create();

            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Updated Name',
                'address' => 'Updated Address',
            ]);

            $response->assertStatus(401);
        });

        test('validates required fields when provided but empty', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => '',
                'address' => '',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'address']);
        });

        test('validates name maximum length', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => str_repeat('a', 256),
                'address' => 'Valid Address',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        test('validates address maximum length', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Valid Name',
                'address' => str_repeat('a', 256),
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['address']);
        });

        test('validates contact_email format when provided', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Valid Name',
                'address' => 'Valid Address',
                'contact_email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['contact_email']);
        });

        test('validates contact_phone maximum length when provided', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Valid Name',
                'address' => 'Valid Address',
                'contact_phone' => str_repeat('1', 21),
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['contact_phone']);
        });

        test('allows partial updates and nullable fields', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);

            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Only Name Updated',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'École mise à jour avec succès.']);

            $response = $this->patchJson(route('school.update', $school), [
                'address' => 'Only Address Updated',
            ]);

            $response->assertStatus(200);
        });

        test('denies access to user without super admin role', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school), [
                'name' => 'Updated Name',
                'address' => 'Updated Address',
            ]);

            $response->assertStatus(403);
        });

        test('denies access to user from different school', function (): void {
            $role = Role::create(['name' => 'super_admin']);
            $school1 = School::factory()->create();
            $school2 = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school1->id]);
            $user->roles()->attach($role);

            $this->actingAs($user);
            $response = $this->patchJson(route('school.update', $school2), [
                'name' => 'Updated Name',
                'address' => 'Updated Address',
            ]);

            $response->assertStatus(403);
        });
    });
});
