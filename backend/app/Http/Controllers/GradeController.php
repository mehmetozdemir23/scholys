<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeRequest;
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
}
