<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Grade;
use Illuminate\Support\Facades\Log;

final class DeactivateGrade
{
    public function handle(Grade $grade): void
    {
        $now = now();

        $grade->update([
            'is_active' => false,
            'deactivated_at' => $now,
        ]);

        Log::info('Grade deactivated', [
            'grade_id' => $grade->id,
            'teacher_id' => $grade->teacher_id,
            'student_id' => $grade->student_id,
            'subject_id' => $grade->subject_id,
            'deactivated_at' => $now,
        ]);
    }
}
