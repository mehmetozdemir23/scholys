<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Mail\UserWelcomeMail;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('CreateUser', function (): void {
    test('creates user with hashed password', function (): void {
        Mail::fake();

        $school = School::factory()->create();
        $plainPassword = 'plainTextPassword123';

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Alice',
            'lastname' => 'Wonderland',
            'email' => 'alice@example.com',
            'password' => $plainPassword,
        ];

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        $user = User::where('email', 'alice@example.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->firstname)->toBe('Alice')
            ->and($user->lastname)->toBe('Wonderland')
            ->and($user->email)->toBe('alice@example.com')
            ->and($user->school_id)->toBe($school->id);

        expect($user->password)->not->toBe($plainPassword)
            ->and(password_verify($plainPassword, $user->password))->toBeTrue();
    });

    test('sends welcome email with plain password', function (): void {
        Mail::fake();

        $school = School::factory()->create();
        $plainPassword = 'welcomePassword456';

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Bob',
            'lastname' => 'Builder',
            'email' => 'bob@example.com',
            'password' => $plainPassword,
        ];

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($attributes) {
            return $mail->hasTo($attributes['email']);
        });
    });

    test('creates user with all provided attributes', function (): void {
        Mail::fake();

        $school = School::factory()->create();

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Charlie',
            'lastname' => 'Brown',
            'email' => 'charlie@example.com',
            'password' => 'securePassword789',
        ];

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        $this->assertDatabaseHas('users', [
            'school_id' => $school->id,
            'firstname' => 'Charlie',
            'lastname' => 'Brown',
            'email' => 'charlie@example.com',
        ]);
    });

    test('preserves plain password for email while hashing for database', function (): void {
        Mail::fake();

        $school = School::factory()->create();
        $originalPassword = 'originalPassword123';

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Diana',
            'lastname' => 'Prince',
            'email' => 'diana@example.com',
            'password' => $originalPassword,
        ];

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        $user = User::where('email', 'diana@example.com')->first();

        expect(password_verify($originalPassword, $user->password))->toBeTrue()
            ->and($user->password)->not->toBe($originalPassword);

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($attributes) {
            return $mail->hasTo($attributes['email']);
        });
    });

    test('handles action instantiation', function (): void {
        $createUser = new CreateUser();

        expect($createUser)->toBeInstanceOf(CreateUser::class);
        expect(method_exists($createUser, 'handle'))->toBeTrue();
    });

    test('handle method accepts array parameter', function (): void {
        Mail::fake();

        $school = School::factory()->create();

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Eva',
            'lastname' => 'Green',
            'email' => 'eva@example.com',
            'password' => 'testPassword456',
        ];

        $createUser = new CreateUser();

        expect(function () use ($createUser, $attributes) {
            $createUser->handle($attributes);
        })->not->toThrow(Exception::class);
    });

    test('sends email to correct recipient', function (): void {
        Mail::fake();

        $school = School::factory()->create();
        $targetEmail = 'frank@example.com';

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Frank',
            'lastname' => 'Sinatra',
            'email' => $targetEmail,
            'password' => 'myWayPassword789',
        ];

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($targetEmail) {
            return $mail->hasTo($targetEmail);
        });
    });

    test('action handles password correctly without mutating original array', function (): void {
        Mail::fake();

        $school = School::factory()->create();
        $originalPassword = 'immutablePassword123';

        $attributes = [
            'school_id' => $school->id,
            'firstname' => 'Grace',
            'lastname' => 'Hopper',
            'email' => 'grace@example.com',
            'password' => $originalPassword,
        ];

        $originalAttributes = $attributes;

        $createUser = new CreateUser();
        $createUser->handle($attributes);

        expect($originalAttributes['password'])->toBe($originalPassword);

        $user = User::where('email', 'grace@example.com')->first();
        expect($user->password)->not->toBe($originalPassword)
            ->and(password_verify($originalPassword, $user->password))->toBeTrue();
    });
});
