<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;

final class GradePolicy
{
    public function create(User $teacher, ClassGroup $classGroup, User $student, Subject $subject): bool
    {
        return $teacher->hasRole('teacher')
            && $teacher->subjects()->where('subject_id', $subject->id)->exists()
            && $teacher->classGroups()->where('class_group_id', $classGroup->id)->exists()
            && $student->hasRole('student')
            && $student->classGroups()->where('class_group_id', $classGroup->id)->exists();
    }

    public function update(User $teacher, Grade $grade): bool
    {
        return $teacher->hasRole('teacher')
            && $teacher->id === $grade->teacher_id
            && $grade->student->classGroups()->where('class_group_id', $grade->class_group_id)->exists()
            && $teacher->subjects()->where('id', $grade->subject_id)->exists();
    }
}
