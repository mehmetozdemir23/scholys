<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class AssignStudentToClassGroup
{
    public function handle(ClassGroup $classGroup, User $user, string $currentUserSchoolId): JsonResponse
    {
        if ($user->school_id !== $currentUserSchoolId) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'L\'utilisateur doit avoir le rôle élève.'], 422);
        }

        if ($classGroup->isFull()) {
            return response()->json(['message' => 'La classe est complète.'], 422);
        }

        if ($classGroup->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'L\'élève est déjà dans cette classe.'], 422);
        }

        $classGroup->students()->attach($user->id, ['assigned_at' => now()]);

        return response()->json([
            'message' => 'Élève affecté à la classe avec succès!',
        ]);
    }
}
