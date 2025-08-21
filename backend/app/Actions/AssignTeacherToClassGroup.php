<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class AssignTeacherToClassGroup
{
    public function handle(ClassGroup $classGroup, User $user, string $currentUserSchoolId): JsonResponse
    {
        if ($user->school_id !== $currentUserSchoolId) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        if (! $user->hasRole('teacher')) {
            return response()->json(['message' => 'L\'utilisateur doit avoir le rôle enseignant.'], 422);
        }

        if ($classGroup->teachers()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'L\'enseignant est déjà assigné à cette classe.'], 422);
        }

        $classGroup->teachers()->attach($user->id, ['assigned_at' => now()]);

        return response()->json([
            'message' => 'Enseignant assigné à la classe avec succès!',
        ]);
    }
}
