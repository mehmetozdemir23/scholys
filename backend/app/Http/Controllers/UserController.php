<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    /**
     * Create a user
     */
    public function store(StoreUserRequest $request, CreateUser $createUser): JsonResponse
    {
        $attributes = $request->validated();

        $schoolId = $request->user()->school_id;

        $createUser->handle(['school_id' => $schoolId, ...$attributes]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès!',
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdateUserPasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => bcrypt($request->input('new_password')),
        ]);

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);
    }
}
