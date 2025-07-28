<?php

declare(strict_types=1);

use App\Actions\SendSchoolInvitation;
use App\Mail\SchoolInvitationMail;
use Illuminate\Support\Facades\Mail;

test('school invitation email is sent', function (): void {
    $email = 'test@example.com';
    Mail::fake();

    $sendSchoolInvitation = new SendSchoolInvitation;
    $sendSchoolInvitation->handle($email);

    Mail::assertSent(SchoolInvitationMail::class, $email);
});
