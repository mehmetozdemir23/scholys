<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class CreateUser
{
    public function handle(array $attributes): void
    {
        $plainPassword = $attributes['password'];
        $attributes['password'] = bcrypt($attributes['password']);

        $user = User::create($attributes);

        Mail::to($user->email)->send(new UserWelcomeMail(
            $user->firstname,
            $user->lastname,
            $user->email,
            $plainPassword
        ));
    }
}
