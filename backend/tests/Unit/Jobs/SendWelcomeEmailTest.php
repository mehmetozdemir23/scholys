<?php

declare(strict_types=1);

use App\Jobs\SendWelcomeEmail;
use App\Mail\UserWelcomeMail;
use Illuminate\Support\Facades\Mail;

describe('SendWelcomeEmail Job', function (): void {
    beforeEach(function (): void {
        Mail::fake();
    });

    test('sends welcome email with correct data', function (): void {
        $userData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'temp_password123',
        ];

        $job = new SendWelcomeEmail($userData);
        $job->handle();

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    });

    test('sends email to correct recipient with correct envelope', function (): void {
        $userData = [
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'another_password456',
        ];

        $job = new SendWelcomeEmail($userData);
        $job->handle();

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            $envelope = $mail->envelope();

            return $mail->hasTo($userData['email'])
                && $envelope->subject === 'Bienvenue sur Scholys';
        });
    });

    test('handles special characters in user data', function (): void {
        $userData = [
            'firstname' => 'Marie-José',
            'lastname' => 'Müller',
            'email' => 'marie.jose@école.fr',
            'password' => 'pássword_wïth_âccents',
        ];

        $job = new SendWelcomeEmail($userData);
        $job->handle();

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    });

    test('sends exactly one email per job execution', function (): void {
        $userData = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'test_password',
        ];

        $job = new SendWelcomeEmail($userData);
        $job->handle();

        Mail::assertSent(UserWelcomeMail::class, 1);
    });

    test('mail content includes user data', function (): void {
        $userData = [
            'firstname' => 'Test',
            'lastname' => 'Content',
            'email' => 'test.content@example.com',
            'password' => 'test_content_password',
        ];

        $job = new SendWelcomeEmail($userData);
        $job->handle();

        Mail::assertSent(UserWelcomeMail::class, function ($mail) use ($userData) {
            $content = $mail->content();
            $data = $content->with;

            return $mail->hasTo($userData['email'])
                && $data['firstname'] === $userData['firstname']
                && $data['lastname'] === $userData['lastname']
                && $data['email'] === $userData['email']
                && $data['password'] === $userData['password'];
        });
    });

    test('job implements ShouldQueue interface', function (): void {
        $userData = [
            'firstname' => 'Queue',
            'lastname' => 'Test',
            'email' => 'queue@example.com',
            'password' => 'queue_password',
        ];

        $job = new SendWelcomeEmail($userData);

        expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
    });
});
