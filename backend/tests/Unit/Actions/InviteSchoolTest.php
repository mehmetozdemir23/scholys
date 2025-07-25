<?php

declare(strict_types=1);

use App\Actions\InviteSchool;
use App\Mail\SchoolInvitationMail;
use Illuminate\Support\Facades\Mail;

test('school invitation email is sent', function (): void {
    $email = 'test@example.com';
    Mail::fake();

    $inviteSchool = new InviteSchool;
    $inviteSchool->handle($email);

    Mail::assertSent(SchoolInvitationMail::class, $email);
});
