<?php

declare(strict_types=1);

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
});
