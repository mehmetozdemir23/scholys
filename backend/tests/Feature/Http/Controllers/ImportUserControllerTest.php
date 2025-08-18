<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

describe('ImportUserController', function (): void {
    beforeEach(function (): void {
        Mail::fake();
        Storage::fake('local');
    });

    test('imports users successfully with valid CSV file', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email\nJohn,Doe,john@example.com\nJane,Smith,jane@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'success_count',
                'error_count',
                'errors',
            ])
            ->assertJson([
                'success_count' => 2,
                'error_count' => 0,
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    });

    test('handles partial import with some errors', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        User::factory()->create(['email' => 'existing@example.com']);

        $csvContent = "firstname,lastname,email\nJohn,Doe,john@example.com\nJane,Smith,existing@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success_count' => 1,
                'error_count' => 1,
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        expect($response->json('errors'))->toHaveCount(1);
    });

    test('requires authentication', function (): void {
        $csvContent = "firstname,lastname,email\nJohn,Doe,john@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(401);
    });

    test('requires super admin role', function (): void {
        $school = School::factory()->create();
        $regularUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email\nJohn,Doe,john@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($regularUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(403);
    });

    test('validates file is required', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['users']);
    });

    test('validates file type is CSV or TXT', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $file = UploadedFile::fake()->create('users.pdf', 100);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['users']);
    });

    test('validates file size limit', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $file = UploadedFile::fake()->create('users.csv', 11000);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['users']);
    });

    test('handles empty CSV file', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success_count' => 0,
                'error_count' => 0,
            ]);
    });

    test('handles CSV with missing required fields', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email\nJohn,,john@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success_count' => 0,
                'error_count' => 1,
            ]);

        expect($response->json('errors'))->toHaveCount(1);
    });

    test('skips empty lines in CSV', function (): void {
        $role = Role::create(['name' => Role::SUPER_ADMIN]);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email\nJohn,Doe,john@example.com\n\n\nJane,Smith,jane@example.com";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success_count' => 2,
                'error_count' => 0,
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    });
});
