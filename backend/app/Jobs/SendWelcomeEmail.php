<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\UserWelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

final class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $userData) {}

    public function handle(): void
    {
        Mail::to($this->userData['email'])->send(
            new UserWelcomeMail(
                $this->userData['firstname'],
                $this->userData['lastname'],
                $this->userData['email'],
                $this->userData['password']
            )
        );
    }
}
