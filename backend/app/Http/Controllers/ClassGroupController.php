<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AssignStudentToClassGroup;
use App\Actions\AssignTeacherToClassGroup;
use App\Actions\CreateClassGroup;
use App\Actions\GetClassGroupStats;
use App\Actions\SearchClassGroups;
use App\Http\Requests\SearchClassGroupRequest;
use App\Http\Requests\StoreClassGroupRequest;
use App\Http\Requests\UpdateClassGroupRequest;
use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class ClassGroupController extends Controller
{
    public function index(SearchClassGroupRequest $request, SearchClassGroups $searchClassGroups): JsonResponse
    {
        $filters = $request->validated();
        $schoolId = $request->user()->school_id;

        $classGroups = $searchClassGroups->handle($filters, $schoolId);

        return response()->json($classGroups);
    }

    public function store(StoreClassGroupRequest $request, CreateClassGroup $createClassGroup): JsonResponse
    {
        $attributes = $request->validated();
        $attributes['school_id'] = $request->user()->school_id;

        $classGroup = $createClassGroup->handle($attributes);

        return response()->json([
            'message' => 'Classe créée avec succès!',
            'class_group' => $classGroup,
        ], 201);
    }

    public function show(ClassGroup $classGroup): JsonResponse
    {
        Gate::authorize('view', $classGroup);

        $classGroup->load(['school', 'students', 'teachers']);

        return response()->json([
            'class_group' => $classGroup,
            'stats' => [
                'student_count' => $classGroup->getCurrentStudentCount(),
                'available_spots' => $classGroup->getAvailableSpots(),
                'is_full' => $classGroup->isFull(),
            ],
        ]);
    }

    public function update(UpdateClassGroupRequest $request, ClassGroup $classGroup): JsonResponse
    {
        Gate::authorize('update', $classGroup);

        $classGroup->update($request->validated());
        $classGroup->load(['school']);

        return response()->json([
            'message' => 'Classe modifiée avec succès!',
            'class_group' => $classGroup,
        ]);
    }

    public function destroy(ClassGroup $classGroup): JsonResponse
    {
        Gate::authorize('delete', $classGroup);

        $classGroup->delete();

        return response()->json([
            'message' => 'Classe supprimée avec succès!',
        ]);
    }

    public function assignStudent(ClassGroup $classGroup, User $user, AssignStudentToClassGroup $assignStudent): JsonResponse
    {
        Gate::authorize('update', $classGroup);

        return $assignStudent->handle($classGroup, $user, auth()->user()->school_id);
    }

    public function removeStudent(ClassGroup $classGroup, User $user): JsonResponse
    {
        Gate::authorize('update', $classGroup);

        if ($user->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $classGroup->students()->detach($user->id);

        return response()->json([
            'message' => 'Élève retiré de la classe avec succès!',
        ]);
    }

    public function assignTeacher(ClassGroup $classGroup, User $user, AssignTeacherToClassGroup $assignTeacher): JsonResponse
    {
        Gate::authorize('update', $classGroup);

        return $assignTeacher->handle($classGroup, $user, auth()->user()->school_id);
    }

    public function removeTeacher(ClassGroup $classGroup, User $user): JsonResponse
    {
        Gate::authorize('update', $classGroup);

        if ($user->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $classGroup->teachers()->detach($user->id);

        return response()->json([
            'message' => 'Enseignant retiré de la classe avec succès!',
        ]);
    }

    public function stats(GetClassGroupStats $getStats): JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $stats = $getStats->handle($schoolId);

        return response()->json($stats);
    }
}
