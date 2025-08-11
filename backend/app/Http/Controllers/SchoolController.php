<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\JsonResponse;

final class SchoolController extends Controller
{
    public function update(UpdateSchoolRequest $request, School $school): JsonResponse
    {
        $school->update($request->validated());

        return response()->json(['message' => 'École mise à jour avec succès.']);
    }
}
