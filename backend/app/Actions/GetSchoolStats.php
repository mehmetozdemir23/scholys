<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class GetSchoolStats
{
    public function handle(User $user): array
    {
        $school = $user->school;

        $totalStudents = $school->students()->count();
        $totalClasses = $school->classGroups()->where('academic_year', getCurrentAcademicYear())->count();
        $totalTeachers = $school->teachers()->count();

        return [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'total_teachers' => $totalTeachers,
        ];
    }
}
