<?php

declare(strict_types=1);

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

describe('ForgotPasswordController', function (): void {
    test('sends reset email for valid email', function (): void {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Si votre adresse e-mail est enregistrée, vous recevrez bientôt un lien pour réinitialiser votre mot de passe.',
            ]);

        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });

        $this->assertDatabaseHas('password_resets', [
            'email' => 'test@example.com',
        ]);
    });

    test('returns validation error for non-existent email', function (): void {
        $response = $this->postJson('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires email field', function (): void {
        $response = $this->postJson('/forgot-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires valid email format', function (): void {
        $response = $this->postJson('/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('stores hashed token in database', function (): void {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        $record = DB::table('password_resets')
            ->where('email', 'test@example.com')
            ->first();

        expect($record)->not->toBeNull();
        expect($record->token)->toHaveLength(60);
        expect($record->created_at)->not->toBeNull();
    });

    test('updates existing token for same email', function (): void {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $firstToken = DB::table('password_resets')
            ->where('email', 'test@example.com')
            ->value('token');

        $this->postJson('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $secondToken = DB::table('password_resets')
            ->where('email', 'test@example.com')
            ->value('token');

        expect($firstToken)->not->toBe($secondToken);

        $count = DB::table('password_resets')
            ->where('email', 'test@example.com')
            ->count();

        expect($count)->toBe(1);
    });

    test('handles multiple users correctly', function (): void {
        Mail::fake();

        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $this->postJson('/forgot-password', ['email' => 'user1@example.com']);
        $this->postJson('/forgot-password', ['email' => 'user2@example.com']);

        Mail::assertSent(PasswordResetMail::class, 2);

        $this->assertDatabaseHas('password_resets', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('password_resets', ['email' => 'user2@example.com']);
    });
});
