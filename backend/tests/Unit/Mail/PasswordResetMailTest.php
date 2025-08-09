<?php

declare(strict_types=1);

use App\Mail\PasswordResetMail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

describe('PasswordResetMail', function (): void {
    test('creates mail with correct token', function (): void {
        $token = 'abc123token456';

        $mail = new PasswordResetMail($token);

        expect($mail)->toBeInstanceOf(PasswordResetMail::class);
    });

    test('envelope has correct from address and subject', function (): void {
        config(['mail.from.address' => 'contact@scholys.com']);
        config(['mail.from.name' => 'Scholys']);

        $mail = new PasswordResetMail('token123');
        $envelope = $mail->envelope();

        expect($envelope)->toBeInstanceOf(Envelope::class)
            ->and($envelope->from)->toBeInstanceOf(Address::class)
            ->and($envelope->from->address)->toBe('contact@scholys.com')
            ->and($envelope->from->name)->toBe('Scholys')
            ->and($envelope->subject)->toBe('Réinitialisation de votre mot de passe - Scholys');
    });

    test('content uses correct view with token data', function (): void {
        $token = 'reset-token-123456';

        $mail = new PasswordResetMail($token);
        $content = $mail->content();

        expect($content)->toBeInstanceOf(Content::class)
            ->and($content->markdown)->toBe('mail.password-reset')
            ->and($content->with)->toBe([
                'token' => $token,
            ]);
    });

    test('has no attachments', function (): void {
        $mail = new PasswordResetMail('token123');
        $attachments = $mail->attachments();

        expect($attachments)->toBeArray()
            ->and($attachments)->toBeEmpty();
    });

    test('uses queueable and serializes models traits', function (): void {
        $mail = new PasswordResetMail('token123');

        expect(class_uses($mail))->toContain(
            Illuminate\Bus\Queueable::class,
            Illuminate\Queue\SerializesModels::class
        );
    });

    test('passes token to view data', function (): void {
        $token = 'unique-reset-token-789';

        $mail = new PasswordResetMail($token);
        $content = $mail->content();

        expect($content->with['token'])->toBe($token);
    });

    test('is final class', function (): void {
        $reflection = new ReflectionClass(PasswordResetMail::class);

        expect($reflection->isFinal())->toBeTrue();
    });

    test('token is private property', function (): void {
        $reflection = new ReflectionClass(PasswordResetMail::class);
        $tokenProperty = $reflection->getProperty('token');

        expect($tokenProperty->isPrivate())->toBeTrue();
    });

    test('constructor accepts string token', function (): void {
        $token = 'test-token-12345';
        $mail = new PasswordResetMail($token);

        $content = $mail->content();
        expect($content->with['token'])->toBe($token);
    });
});
