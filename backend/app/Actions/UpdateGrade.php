<?php

namespace App\Actions;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UpdateGrade
{
    public function handle(Grade $grade, array $attributes): void
    {
        $grade->update($attributes);

        Log::info('Grade updated', [
            'grade_id' => $grade->id,
            'teacher_id' => $grade->teacher_id,
            'student_id' => $grade->student_id,
            'subject_id' => $grade->subject_id,
            'updated_attributes' => $attributes,
            'modified_at' => now()
        ]);
    }
}
