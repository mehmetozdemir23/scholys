<?php

declare(strict_types=1);

use App\Mail\UserWelcomeMail;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createSuperAdmin(): User
{
    /** @var School $school */
    $school = School::factory()->create();

    /** @var Role $superAdminRole */
    $superAdminRole = Role::factory()->create(['name' => Role::SUPER_ADMIN]);

    /** @var User $admin */
    $admin = User::factory()->create(['school_id' => $school->id]);
    $admin->roles()->attach($superAdminRole);

    return $admin;
}

describe('POST /users', function (): void {
    test('admin can create a user successfully', function (): void {
        Mail::fake();

        $admin = createSuperAdmin();

        $userData = [
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/users', $userData);

        $response->assertOk()
            ->assertJson(['message' => 'Utilisateur créé avec succès!']);

        $this->assertDatabaseHas('users', [
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'school_id' => $admin->school_id,
        ]);

        $createdUser = User::where('email', 'jean.dupont@example.com')->first();
        expect($createdUser->password)->not->toBe('motdepasse123');
        expect(password_verify('motdepasse123', $createdUser->password))->toBeTrue();

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    });

    test('user is created in same school as admin', function (): void {
        Mail::fake();

        $admin = createSuperAdmin();
        $school2 = School::factory()->create();

        $userData = [
            'firstname' => 'Marie',
            'lastname' => 'Martin',
            'email' => 'marie.martin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($admin)
            ->postJson('/api/users', $userData);

        $createdUser = User::where('email', 'marie.martin@example.com')->first();
        expect($createdUser->school_id)->toBe($admin->school_id);
        expect($createdUser->school_id)->not->toBe($school2->id);
    });

    test('welcome email is sent with correct data', function (): void {
        Mail::fake();

        $admin = createSuperAdmin();

        $userData = [
            'firstname' => 'Pierre',
            'lastname' => 'Durand',
            'email' => 'pierre.durand@example.com',
            'password' => 'testpassword123',
            'password_confirmation' => 'testpassword123',
        ];

        $this->actingAs($admin)
            ->postJson('/api/users', $userData);

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    });

    test('requires authentication', function (): void {
        $userData = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(401);
    });

    test('requires firstname', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'lastname' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['firstname']);
    });

    test('requires lastname', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lastname']);
    });

    test('requires email', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires valid email format', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires unique email', function (): void {
        $admin = createSuperAdmin();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires password', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires password confirmation', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires password minimum length', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires matching password confirmation', function (): void {
        $admin = createSuperAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });
});
