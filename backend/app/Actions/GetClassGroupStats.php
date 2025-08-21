<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ClassGroup;

final class GetClassGroupStats
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $schoolId): array
    {
        return [
            'total' => ClassGroup::forSchool($schoolId)->count(),
            'active' => ClassGroup::forSchool($schoolId)->active()->count(),
            'inactive' => ClassGroup::forSchool($schoolId)->where('is_active', false)->count(),
            'by_level' => ClassGroup::forSchool($schoolId)
                ->active()
                ->selectRaw('level, COUNT(*) as count')
                ->whereNotNull('level')
                ->groupBy('level')
                ->orderBy('level')
                ->get()
                ->pluck('count', 'level'),
        ];
    }
}
