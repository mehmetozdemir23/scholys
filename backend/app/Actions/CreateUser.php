<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class CreateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): void
    {
        /** @var string $plainPassword */
        $plainPassword = $attributes['password'];
        $attributes['password'] = bcrypt($plainPassword);

        /** @var array<string, mixed> $attributes */
        $user = User::create($attributes);

        /** @var string $firstname */
        $firstname = $user->firstname;
        /** @var string $lastname */
        $lastname = $user->lastname;

        Mail::to($user->email)->send(new UserWelcomeMail(
            $firstname,
            $lastname,
            $user->email,
            $plainPassword
        ));
    }
}
