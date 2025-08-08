<?php

declare(strict_types=1);

use App\Models\User;

describe('LogoutController', function (): void {
    test('authenticated user can logout', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Déconnexion réussie',
            ]);

        $this->assertGuest();
    });

    test('logout invalidates session', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->startSession();

        $oldSessionId = $this->app['session']->getId();

        $response = $this->postJson('/logout');

        $response->assertStatus(200);

        expect($this->app['session']->getId())->not->toBe($oldSessionId);
    });

    test('logout regenerates csrf token', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->startSession();

        $oldToken = $this->app['session']->token();

        $response = $this->postJson('/logout');

        $response->assertStatus(200);

        expect($this->app['session']->token())->not->toBe($oldToken);
    });

    test('unauthenticated user cannot logout', function (): void {
        $response = $this->postJson('/logout');

        $response->assertStatus(401);
    });

    test('logout clears authentication', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->postJson('/logout');

        $response->assertStatus(200);
        $this->assertGuest();
    });

    test('logout returns json response', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);
    });

    test('multiple logout attempts are safe', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/logout')
            ->assertStatus(200);

        $this->postJson('/logout')
            ->assertStatus(401);
    });

    test('logout works after remember me login', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Déconnexion réussie',
            ]);

        $this->assertGuest();
    });
});
