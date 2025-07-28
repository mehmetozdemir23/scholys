<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\SchoolInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

final class SendSchoolInvitation
{
    /**
     * Send an invitation to a school.
     */
    public function handle(string $email): void
    {
        $url = URL::temporarySignedRoute(
            'school.register',
            now()->addHour(),
            ['token' => $email]
        );

        Mail::to($email)->send(new SchoolInvitationMail($email, $url));
    }
}
