<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

describe('SchoolRegistrationController', function (): void {
    describe('sendInvitation', function (): void {
        test('can send an invitation', function (): void {
            Mail::fake();

            $response = $this->postJson(route('school.invite'), [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Invitation envoyée avec succès.']);

            Mail::assertSent(App\Mail\SchoolInvitationMail::class);
        });

        test('validates email is required', function (): void {
            $response = $this->postJson(route('school.invite'), []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('validates email format', function (): void {
            $response = $this->postJson(route('school.invite'), [
                'email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('validates email is unique', function (): void {
            User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson(route('school.invite'), [
                'email' => 'existing@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns error when mail sending fails', function (): void {
            Mail::fake();
            Mail::shouldReceive('to')->andThrow(new Exception('Mail server error'));

            $response = $this->postJson(route('school.invite'), [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(500)
                ->assertJson(['message' => 'Échec de l\'envoi de l\'invitation : Mail server error']);
        });
    });

    describe('completeAccountSetup', function (): void {
        test('redirects to frontend on successful account setup', function (): void {
            Role::create(['name' => 'super_admin']);

            $email = 'admin@school.com';
            $url = URL::temporarySignedRoute(
                'school.register',
                now()->addHour(),
                ['token' => $email]
            );

            $response = $this->get($url);

            $user = User::firstWhere('email', $email);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=success')
                ->assertRedirectContains('user_email='.urlencode($email))
                ->assertRedirectContains('token=');

            expect(User::where('email', $email)->exists())->toBeTrue();
        });

        test('redirects with error for invalid signature', function (): void {

            $invalidUrl = route('school.register', ['token' => 'email']);

            $response = $this->get($invalidUrl);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=error')
                ->assertRedirectContains(http_build_query([
                    'message' => 'Lien d\'invitation invalide ou expiré.',
                ]));
        });

        test('redirects with error for expired signature', function (): void {
            $email = 'admin@school.com';
            $url = URL::temporarySignedRoute(
                'school.register',
                now()->subHours(1),
                ['token' => $email]
            );

            $response = $this->get($url);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=error')
                ->assertRedirectContains(http_build_query([
                    'message' => 'Lien d\'invitation invalide ou expiré.',
                ]));
        });

        test('redirects with error when account setup throws exception', function (): void {

            $email = 'admin@school.com';
            $url = URL::temporarySignedRoute(
                'school.register',
                now()->addHour(),
                ['token' => $email]
            );

            $response = $this->get($url);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=error')
                ->assertRedirectContains('%C3%89chec+de+la+confirmation+d%27inscription');
        });

    });

    describe('resetPasswordAfterInvitation', function (): void {
        test('resets password after invitation', function (): void {
            $user = User::factory()->create(['email' => 'admin@school.com']);
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)->postJson(route('school.registration.reset-password'), [
                'email' => $user->email,
                'password' => 'new_password123',
                'password_confirmation' => 'new_password123',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Mot de passe réinitialisé avec succès.']);

            $user->refresh();
            expect(Hash::check('new_password123', $user->password))->toBeTrue();
        });

        test('requires authentication for password reset', function (): void {
            $response = $this->postJson(route('school.registration.reset-password'), [
                'email' => 'admin@school.com',
                'password' => 'new_password123',
                'password_confirmation' => 'new_password123',
            ]);
            $response->assertStatus(401);
        });

        test('validates password is required for reset', function (): void {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $response = $this->withToken($token)->postJson(route('school.registration.reset-password'), []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('validates password minimum length for reset', function (): void {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $response = $this->withToken($token)->postJson(route('school.registration.reset-password'), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('validates password confirmation for reset', function (): void {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)->postJson(route('school.registration.reset-password'), [
                'password' => 'valid_password123',
                'password_confirmation' => 'different_password',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });
    });
});
