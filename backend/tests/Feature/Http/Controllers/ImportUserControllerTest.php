<?php

declare(strict_types=1);

use App\Jobs\ImportUsers;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('ImportUserController', function (): void {
    beforeEach(function (): void {
        Mail::fake();
        Queue::fake();
        Storage::fake('local');
    });

    test('dispatches import job with valid CSV file', function (): void {
        $role = Role::create(['name' => 'super_admin']);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\nJane,Smith,jane@example.com,staff";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Import des utilisateurs en cours. Vous recevrez une notification une fois terminé.',
                'status' => 'processing',
            ]);

        Queue::assertPushed(ImportUsers::class);
    });

    test('dispatches import job even with validation errors in CSV', function (): void {
        $role = Role::create(['name' => 'super_admin']);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\nJane,Smith,invalid-email,staff";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Import des utilisateurs en cours. Vous recevrez une notification une fois terminé.',
                'status' => 'processing',
            ]);

        Queue::assertPushed(ImportUsers::class);
    });

    test('requires authentication', function (): void {
        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(401);
    });

    test('requires super admin role', function (): void {
        $school = School::factory()->create();
        $regularUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($regularUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(403);
    });

    test('validates file is required', function (): void {
        $role = Role::create(['name' => 'super_admin']);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['users']);
    });

    test('validates file type is CSV or TXT', function (): void {
        $role = Role::create(['name' => 'super_admin']);
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
        $role = Role::create(['name' => 'super_admin']);
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

    test('dispatches job with empty CSV file', function (): void {
        $role = Role::create(['name' => 'super_admin']);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email,role\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Import des utilisateurs en cours. Vous recevrez une notification une fois terminé.',
                'status' => 'processing',
            ]);

        Queue::assertPushed(ImportUsers::class);
    });

    test('validates required role field in CSV', function (): void {
        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'staff']);

        $role = Role::create(['name' => 'super_admin']);
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        $adminUser->roles()->attach($role);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\nJane,Smith,jane@example.com,invalid_role";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $this->actingAs($adminUser);
        $response = $this->postJson('/api/users/import', [
            'users' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Import des utilisateurs en cours. Vous recevrez une notification une fois terminé.',
                'status' => 'processing',
            ]);

        Queue::assertPushed(ImportUsers::class);
    });
});
