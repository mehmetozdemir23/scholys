<?php

declare(strict_types=1);

use App\Mail\SchoolInvitationMail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

describe('SchoolInvitationMail', function (): void {
    test('creates mail with correct email and url', function (): void {
        $email = 'admin@school.com';
        $url = 'https://app.com/register/token123';

        $mail = new SchoolInvitationMail($email, $url);

        expect($mail)->toBeInstanceOf(SchoolInvitationMail::class);
    });

    test('envelope has correct from address and subject', function (): void {
        config(['mail.from.address' => 'contact@scholys.com']);
        config(['mail.from.name' => 'Scholys']);

        $mail = new SchoolInvitationMail('admin@school.com', 'https://app.com/token123');
        $envelope = $mail->envelope();

        expect($envelope)->toBeInstanceOf(Envelope::class)
            ->and($envelope->from)->toBeInstanceOf(Address::class)
            ->and($envelope->from->address)->toBe('contact@scholys.com')
            ->and($envelope->from->name)->toBe('Scholys')
            ->and($envelope->subject)->toBe('Invitation à rejoindre Scholys');
    });

    test('content uses correct view with data', function (): void {
        $email = 'admin@school.com';
        $url = 'https://app.com/register/token123';

        $mail = new SchoolInvitationMail($email, $url);
        $content = $mail->content();

        expect($content)->toBeInstanceOf(Content::class)
            ->and($content->markdown)->toBe('mail.school-invitation')
            ->and($content->with)->toBe([
                'email' => $email,
                'url' => $url,
            ]);
    });

    test('has no attachments', function (): void {
        $mail = new SchoolInvitationMail('admin@school.com', 'https://app.com/token');
        $attachments = $mail->attachments();

        expect($attachments)->toBeArray()
            ->and($attachments)->toBeEmpty();
    });

    test('uses queueable and serializes models traits', function (): void {
        $mail = new SchoolInvitationMail('admin@school.com', 'https://app.com/token');

        expect(class_uses($mail))->toContain(
            Illuminate\Bus\Queueable::class,
            Illuminate\Queue\SerializesModels::class
        );
    });

    test('passes email and url to view data', function (): void {
        $email = 'test@example.com';
        $url = 'https://frontend.com/confirm/abc123';

        $mail = new SchoolInvitationMail($email, $url);
        $content = $mail->content();

        expect($content->with['email'])->toBe($email)
            ->and($content->with['url'])->toBe($url);
    });

    test('is final class', function (): void {
        $reflection = new ReflectionClass(SchoolInvitationMail::class);

        expect($reflection->isFinal())->toBeTrue();
    });
});
