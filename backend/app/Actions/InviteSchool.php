<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\SchoolInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

final class InviteSchool
{
    /**
     * Send an invitation to a school.
     */
    public function handle(string $email): void
    {
        $url = URL::temporarySignedRoute(
            'school.confirm',
            now()->addMinutes(60),
            ['token' => $email]
        );

        Mail::to($email)->send(new SchoolInvitationMail($email, $url));
    }
}
