<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;

it('can send an invitation', function (): void {
    $response = $this->postJson(route('school.invite'), [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Invitation sent successfully.']);
});

it('can confirm an invitation with a valid signature', function (): void {
    $email = 'test@example.com';
    $url = URL::temporarySignedRoute(
        'school.confirm',
        now()->addMinutes(60),
        ['token' => $email]
    );
    $response = $this->getJson($url);
    $response->assertStatus(200)
        ->assertJson(['message' => 'Invitation confirmed successfully.']);
});

it('cannot confirm an invitation with an invalid signature', function () {
    $response = $this->getJson(route('school.confirm', ['token' => 'invalid']));
    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid or expired invitation link.']);
});

it('returns an error when sending an invitation fails', function (): void {
    Mail::fake();
    Mail::shouldReceive('to')
        ->andThrow(new Exception('Mail server error'));

    $response = $this->postJson(route('school.invite'), [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(500)
        ->assertJson(['message' => 'Failed to send invitation email: Mail server error']);
});
