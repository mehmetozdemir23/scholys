<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateGrade;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class GradeController extends Controller
{
    public function store(StoreGradeRequest $request, ClassGroup $classGroup, User $student, Subject $subject): JsonResponse
    {
        $data = $request->validated();
        $data['student_id'] = $student->id;
        $data['teacher_id'] = $request->user()->id;
        $data['class_group_id'] = $classGroup->id;
        $data['subject_id'] = $subject->id;

        Grade::create($data);

        return response()->json([
            'message' => 'Note ajoutée avec succès!',
        ]);
    }

    public function update(UpdateGradeRequest $request, ClassGroup $classGroup, User $student, Subject $subject, Grade $grade, UpdateGrade $updateGrade): JsonResponse
    {
        $updateGrade->handle($grade, $request->validated());

        return response()->json([
            'message' => 'Note modifiée avec succès!',
        ]);
    }
}
