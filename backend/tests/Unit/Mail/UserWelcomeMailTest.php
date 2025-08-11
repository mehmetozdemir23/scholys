<?php

declare(strict_types=1);

use App\Mail\UserWelcomeMail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

describe('UserWelcomeMail', function (): void {
    test('creates mail with correct user data and password', function (): void {
        $firstname = 'Jean';
        $lastname = 'Dupont';
        $email = 'jean.dupont@example.com';
        $password = 'tempPassword123';

        $mail = new UserWelcomeMail($firstname, $lastname, $email, $password);

        expect($mail)->toBeInstanceOf(UserWelcomeMail::class);
    });

    test('envelope has correct from address and subject', function (): void {
        config(['mail.from.address' => 'contact@scholys.com']);
        config(['mail.from.name' => 'Scholys']);

        $mail = new UserWelcomeMail('Jean', 'Dupont', 'jean@example.com', 'password123');
        $envelope = $mail->envelope();

        expect($envelope)->toBeInstanceOf(Envelope::class)
            ->and($envelope->from)->toBeInstanceOf(Address::class)
            ->and($envelope->from->address)->toBe('contact@scholys.com')
            ->and($envelope->from->name)->toBe('Scholys')
            ->and($envelope->subject)->toBe('Bienvenue sur Scholys');
    });

    test('content uses correct view with user data', function (): void {
        $firstname = 'Marie';
        $lastname = 'Martin';
        $email = 'marie.martin@example.com';
        $password = 'welcomePassword456';

        $mail = new UserWelcomeMail($firstname, $lastname, $email, $password);
        $content = $mail->content();

        expect($content)->toBeInstanceOf(Content::class)
            ->and($content->markdown)->toBe('mail.user-welcome')
            ->and($content->with)->toBe([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
            ]);
    });

    test('has no attachments', function (): void {
        $mail = new UserWelcomeMail('Test', 'User', 'test@example.com', 'password');
        $attachments = $mail->attachments();

        expect($attachments)->toBeArray()
            ->and($attachments)->toBeEmpty();
    });

    test('uses queueable and serializes models traits', function (): void {
        $mail = new UserWelcomeMail('Test', 'User', 'test@example.com', 'password');

        expect(class_uses($mail))->toContain(
            Illuminate\Bus\Queueable::class,
            Illuminate\Queue\SerializesModels::class
        );
    });

    test('passes all required data to view', function (): void {
        $firstname = 'Pierre';
        $lastname = 'Durand';
        $email = 'pierre.durand@example.com';
        $password = 'userPassword789';

        $mail = new UserWelcomeMail($firstname, $lastname, $email, $password);
        $content = $mail->content();

        expect($content->with['firstname'])->toBe($firstname)
            ->and($content->with['lastname'])->toBe($lastname)
            ->and($content->with['email'])->toBe($email)
            ->and($content->with['password'])->toBe($password);
    });

    test('is final class', function (): void {
        $reflection = new ReflectionClass(UserWelcomeMail::class);

        expect($reflection->isFinal())->toBeTrue();
    });

    test('constructor parameters are private', function (): void {
        $reflection = new ReflectionClass(UserWelcomeMail::class);
        $constructor = $reflection->getConstructor();

        expect($constructor)->not->toBeNull();

        $parameters = $constructor->getParameters();
        expect($parameters)->toHaveCount(4);

        foreach ($parameters as $parameter) {

            $propertyName = $parameter->getName();
            $property = $reflection->getProperty($propertyName);
            expect($property->isPrivate())->toBeTrue();
        }
    });

    test('constructor accepts correct parameter types', function (): void {
        $firstname = 'Sophie';
        $lastname = 'Bernard';
        $email = 'sophie.bernard@example.com';
        $password = 'testPassword123';

        $mail = new UserWelcomeMail($firstname, $lastname, $email, $password);
        $content = $mail->content();

        expect($content->with)->toMatchArray([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,
        ]);
    });
});
