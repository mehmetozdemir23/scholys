<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\Role;
use App\Models\School;
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
                ->assertJson(['message' => 'Invitation sent successfully.']);

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
                ->assertJson(['message' => 'Failed to send invitation email: Mail server error']);
        });
    });

    describe('completeAccountSetup', function (): void {
        test('redirects to frontend on successful account setup', function (): void {
            Role::create(['name' => Role::SUPER_ADMIN]);

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
                    'message' => 'Invalid or expired invitation link.',
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
                    'message' => 'Invalid or expired invitation link.',
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
                ->assertRedirectContains('Failed+to+confirm+school+registration');
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
                ->assertJson(['message' => 'Password reset successfully.']);

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

    describe('selectPlan', function (): void {
        test('can select a plan for school', function (): void {
            $plan = Plan::factory()->create();
            $school = School::factory()->create(['plan_id' => null]);
            $user = User::factory()->create(['school_id' => $school->id]);
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)
                ->postJson(route('school.registration.select-plan'), [
                    'plan_id' => $plan->id,
                ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Plan selected successfully.']);

            $school->refresh();
            expect($school->plan_id)->toBe($plan->id);
        });

        test('requires authentication to select plan', function (): void {
            $plan = Plan::factory()->create();

            $response = $this->postJson(route('school.registration.select-plan'), [
                'plan_id' => $plan->id,
            ]);

            $response->assertStatus(401);
        });

        test('validates plan_id is required', function (): void {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)
                ->postJson(route('school.registration.select-plan'), []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['plan_id']);
        });

        test('validates plan_id exists in plans table', function (): void {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)
                ->postJson(route('school.registration.select-plan'), [
                    'plan_id' => 'non-existent-id',
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['plan_id']);
        });

        test('updates existing plan when school already has one', function (): void {
            $oldPlan = Plan::factory()->create();
            $newPlan = Plan::factory()->create();
            $school = School::factory()->create(['plan_id' => $oldPlan->id]);
            $user = User::factory()->create(['school_id' => $school->id]);
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withToken($token)
                ->postJson(route('school.registration.select-plan'), [
                    'plan_id' => $newPlan->id,
                ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Plan selected successfully.']);

            $school->refresh();
            expect($school->plan_id)->toBe($newPlan->id);
        });

    });
});
