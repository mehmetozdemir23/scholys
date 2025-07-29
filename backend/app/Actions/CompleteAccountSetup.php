<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CompleteAccountSetup
{
    /**
     * Handle the completion of account setup.
     *
     * @return array{user_id: string, user_email: string}
     *
     * @throws InvalidSignatureException When invitation link is invalid/expired
     * @throws Exception When user/school creation fails
     */
    public function handle(Request $request): array
    {
        if (! $request->hasValidSignature()) {
            throw new InvalidSignatureException;
        }

        $user = null;

        /** @var string $email */
        $email = $request->query('token');

        DB::transaction(function () use (&$user, $email): void {
            $school = School::create();
            $user = User::create([
                'email' => $email,
                'school_id' => $school->id,
                'password' => bcrypt(Str::random(32)), // Temporary password
            ]);
            $user->assignRole(Role::SUPER_ADMIN);
        });

        /** @var User $user */
        return [
            'user_id' => (string) $user->id,
            'user_email' => $email,
        ];
    }
}
