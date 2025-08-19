<?php

declare(strict_types=1);

use App\Models\User;

describe('LoginController', function (): void {
    test('user can login with valid credentials', function (): void {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Connexion réussie',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);

        $this->assertAuthenticatedAs($user);
    });

    test('user can login with remember me', function (): void {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember_me' => true,
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    });

    test('user cannot login with invalid email', function (): void {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Les identifiants fournis sont incorrects.',
                'errors' => [
                    'email' => ['Les identifiants fournis sont incorrects.'],
                ],
            ]);

        $this->assertGuest();
    });

    test('user cannot login with invalid password', function (): void {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Les identifiants fournis sont incorrects.',
                'errors' => [
                    'email' => ['Les identifiants fournis sont incorrects.'],
                ],
            ]);

        $this->assertGuest();
    });

    test('login requires email', function (): void {
        $response = $this->postJson('/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('login requires password', function (): void {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('login requires valid email format', function (): void {
        $response = $this->postJson('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('remember me must be boolean', function (): void {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember_me' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['remember_me']);
    });

    test('session is regenerated on successful login', function (): void {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->startSession();
        $oldSessionId = $this->app['session']->getId();

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        expect($this->app['session']->getId())->not->toBe($oldSessionId);
    });

    test('response contains user data', function (): void {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                ],
            ]);
    });
});
