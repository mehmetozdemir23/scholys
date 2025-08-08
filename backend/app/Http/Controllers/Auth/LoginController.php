<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        if (
            auth()->attempt(
                $request->safe()->only('email', 'password'),
                $request->safe()->boolean('remember_me')
            )
        ) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = auth()->user();

            return response()->json([
                'message' => 'Connexion réussie',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Les identifiants fournis sont incorrects.',
            'errors' => [
                'email' => ['Les identifiants fournis sont incorrects.'],
            ],
        ], 422);
    }
}
