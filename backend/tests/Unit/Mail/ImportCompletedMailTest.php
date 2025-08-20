<?php

declare(strict_types=1);

use App\Mail\ImportCompletedMail;
use App\Models\User;

describe('ImportCompletedMail', function (): void {
    test('mail is built correctly with successful import', function (): void {
        $user = User::factory()->make([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $result = [
            'successCount' => 10,
            'errorCount' => 0,
            'errors' => [],
        ];

        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();
        $data = $content->with;

        expect($data['successCount'])->toBe(10)
            ->and($data['errorCount'])->toBe(0)
            ->and($data['hasErrors'])->toBeFalse()
            ->and($data['errors'])->toBeEmpty()
            ->and($data['user'])->toBe($user);
    });

    test('mail is built correctly with partial failures', function (): void {
        $user = User::factory()->make([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $errors = [
            [
                'line' => 2,
                'data' => ['firstname' => 'Invalid', 'lastname' => '', 'email' => 'invalid@test.com'],
                'error' => 'The lastname field is required.',
            ],
            [
                'line' => 3,
                'data' => ['firstname' => 'Bad', 'lastname' => 'Email', 'email' => 'not-an-email'],
                'error' => 'The email must be a valid email address.',
            ],
        ];

        $result = [
            'successCount' => 8,
            'errorCount' => 2,
            'errors' => $errors,
        ];

        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();
        $data = $content->with;

        expect($data['successCount'])->toBe(8)
            ->and($data['errorCount'])->toBe(2)
            ->and($data['hasErrors'])->toBeTrue()
            ->and($data['errors'])->toBe($errors)
            ->and($data['user'])->toBe($user);
    });

    test('mail is built correctly with complete failure', function (): void {
        $user = User::factory()->make([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
        ]);

        $errors = [
            [
                'line' => 2,
                'data' => ['firstname' => '', 'lastname' => 'Test', 'email' => 'test@example.com'],
                'error' => 'The firstname field is required.',
            ],
        ];

        $result = [
            'successCount' => 0,
            'errorCount' => 1,
            'errors' => $errors,
        ];

        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();
        $data = $content->with;

        expect($data['successCount'])->toBe(0)
            ->and($data['errorCount'])->toBe(1)
            ->and($data['hasErrors'])->toBeTrue()
            ->and($data['errors'])->toHaveCount(1);
    });

    test('mail has correct subject', function (): void {
        $user = User::factory()->make();
        $result = ['successCount' => 5, 'errorCount' => 0, 'errors' => []];

        $mail = new ImportCompletedMail($result, $user);
        $envelope = $mail->envelope();

        expect($envelope->subject)->toBe('Import d\'utilisateurs terminé - Scholys');
    });

    test('mail uses correct view', function (): void {
        $user = User::factory()->make();
        $result = ['successCount' => 3, 'errorCount' => 1, 'errors' => []];

        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();

        expect($content->view)->toBe('emails.import-completed');
    });

    test('hasErrors property is calculated correctly', function (): void {
        $user = User::factory()->make();

        $mailWithoutErrors = new ImportCompletedMail([
            'successCount' => 5,
            'errorCount' => 0,
            'errors' => [],
        ], $user);

        $mailWithErrors = new ImportCompletedMail([
            'successCount' => 3,
            'errorCount' => 2,
            'errors' => [['line' => 2, 'error' => 'Some error']],
        ], $user);

        $contentWithoutErrors = $mailWithoutErrors->content();
        $contentWithErrors = $mailWithErrors->content();

        expect($contentWithoutErrors->with['hasErrors'])->toBeFalse()
            ->and($contentWithErrors->with['hasErrors'])->toBeTrue();
    });

    test('mail handles empty result arrays', function (): void {
        $user = User::factory()->make();
        $result = [
            'successCount' => 0,
            'errorCount' => 0,
            'errors' => [],
        ];

        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();
        $data = $content->with;

        expect($data['successCount'])->toBe(0)
            ->and($data['errorCount'])->toBe(0)
            ->and($data['hasErrors'])->toBeFalse()
            ->and($data['errors'])->toBeEmpty();
    });

    test('mail preserves user data for template', function (): void {
        $user = User::factory()->make([
            'firstname' => 'Marie',
            'lastname' => 'Dubois',
            'email' => 'marie.dubois@school.fr',
        ]);

        $result = ['successCount' => 15, 'errorCount' => 3, 'errors' => []];
        $mail = new ImportCompletedMail($result, $user);
        $content = $mail->content();
        $userData = $content->with['user'];

        expect($userData->firstname)->toBe('Marie')
            ->and($userData->lastname)->toBe('Dubois')
            ->and($userData->email)->toBe('marie.dubois@school.fr');
    });
});
