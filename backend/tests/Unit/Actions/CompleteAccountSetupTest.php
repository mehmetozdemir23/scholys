<?php

declare(strict_types=1);

use App\Actions\CompleteAccountSetup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;

test('handles account creation', function (): void {
    Role::create(['name' => Role::SUPER_ADMIN]);

    $email = 'admin@school.com';
    $url = URL::temporarySignedRoute(
        'school.register',
        now()->addHour(),
        ['token' => $email]
    );

    $request = Request::create($url);
    $completeAccountSetup = new CompleteAccountSetup;

    $result = $completeAccountSetup->handle($request);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('user_id')
        ->and($result['user_id'])->toBeUuid();
    expect($result)->toHaveKey('user_email')
        ->and($result['user_email'])->toBe($email);
    expect(User::find($result['user_id'])->email)->toBe($email);
    expect(User::find($result['user_id'])->hasRole(Role::SUPER_ADMIN))->toBeTrue();
});

test('throws exception for invalid signature', function (): void {
    // Signature and expires parameters missing from the url - this simulates an invalid signature
    $request = Request::create('/school/register?token=admin@school.com');
    $completeAccountSetup = new CompleteAccountSetup;

    expect(fn () => $completeAccountSetup->handle($request))
        ->toThrow(InvalidSignatureException::class);
});

test('throws exception for expired signature', function (): void {
    $email = 'admin@school.com';
    $url = URL::temporarySignedRoute(
        'school.register',
        now()->subHours(1),
        ['token' => $email]
    );

    $request = Request::create($url);
    $completeAccountSetup = new CompleteAccountSetup;

    expect(fn () => $completeAccountSetup->handle($request))
        ->toThrow(InvalidSignatureException::class);
});
