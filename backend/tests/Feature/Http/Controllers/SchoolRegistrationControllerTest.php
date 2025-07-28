<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

describe('SchoolRegistrationController', function () {
    describe('sendInvitation', function () {
        it('can send an invitation', function (): void {
            Mail::fake();

            $response = $this->postJson(route('school.invite'), [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Invitation sent successfully.']);

            Mail::assertSent(App\Mail\SchoolInvitationMail::class);
        });

        it('validates email is required', function (): void {
            $response = $this->postJson(route('school.invite'), []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates email format', function (): void {
            $response = $this->postJson(route('school.invite'), [
                'email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates email is unique', function (): void {
            User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson(route('school.invite'), [
                'email' => 'existing@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('returns error when mail sending fails', function (): void {
            Mail::fake();
            Mail::shouldReceive('to')->andThrow(new Exception('Mail server error'));

            $response = $this->postJson(route('school.invite'), [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(500)
                ->assertJson(['message' => 'Failed to send invitation email: Mail server error']);
        });
    });

    describe('completeAccountSetup', function () {
        it('redirects to frontend on successful account setup', function (): void {
            Role::create(['name' => Role::SUPER_ADMIN]);

            $email = 'admin@school.com';
            $url = URL::temporarySignedRoute(
                'school.register',
                now()->addHour(),
                ['token' => $email]
            );

            $response = $this->get($url);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=success')
                ->assertRedirectContains('user_email='.urlencode($email));

            $this->assertAuthenticated();
            expect(User::where('email', $email)->exists())->toBeTrue();
        });

        it('redirects with error for invalid signature', function (): void {
            // This simulates an invalid signature - the token is not signed
            $invalidUrl = route('school.register', ['token' => 'email']);

            $response = $this->get($invalidUrl);

            $response->assertStatus(302)
                ->assertRedirect()
                ->assertRedirectContains('status=error')
                ->assertRedirectContains(http_build_query([
                    'message' => 'Invalid or expired invitation link.',
                ]));
        });

        it('redirects with error for expired signature', function (): void {
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
                    'message' => 'Invalid or expired invitation link.',
                ]));
        });

        it('redirects with error when account setup fails', function (): void {
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
                ->assertRedirectContains(http_build_query([
                    'message' => 'Failed to confirm school registration: ',
                ]));
        });
    });
});
