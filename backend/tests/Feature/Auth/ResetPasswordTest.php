<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('POST /reset-password', function (): void {
    test('successfully resets password with valid token', function (): void {
        $user = User::factory()->create();
        $token = 'valid-reset-token';

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => bcrypt($token),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Mot de passe réinitialisé avec succès.']);

        $user->refresh();
        expect(Hash::check('nouveaumotdepasse', $user->password))->toBeTrue();

        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email,
        ]);
    });

    test('fails with invalid token', function (): void {
        $user = User::factory()->create();

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => bcrypt('valid-token'),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Token invalide ou expiré.']);
    });

    test('fails with non-existent email', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'nonexistent@example.com',
            'token' => 'any-token',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Token invalide ou expiré.']);
    });

    test('fails with expired token', function (): void {
        $user = User::factory()->create();
        $token = 'expired-token';

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => bcrypt($token),
            'created_at' => Carbon::now()->subMinutes(65),
        ]);

        $response = $this->postJson('/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Token invalide ou expiré.']);

        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email,
        ]);
    });

    test('fails when user does not exist', function (): void {
        $token = 'valid-token';

        DB::table('password_resets')->insert([
            'email' => 'deleted-user@example.com',
            'token' => bcrypt($token),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/reset-password', [
            'email' => 'deleted-user@example.com',
            'token' => $token,
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Utilisateur introuvable.']);
    });

    test('requires email field', function (): void {
        $response = $this->postJson('/reset-password', [
            'token' => 'some-token',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('requires token field', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'test@example.com',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('requires password field', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'test@example.com',
            'token' => 'some-token',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires password confirmation', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'test@example.com',
            'token' => 'some-token',
            'password' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires password minimum length', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'test@example.com',
            'token' => 'some-token',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('requires matching password confirmation', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'test@example.com',
            'token' => 'some-token',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('validates email format', function (): void {
        $response = $this->postJson('/reset-password', [
            'email' => 'invalid-email',
            'token' => 'some-token',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});