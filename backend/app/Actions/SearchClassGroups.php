<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ClassGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class SearchClassGroups
{
    /**
     * @return LengthAwarePaginator<int, ClassGroup>
     */
    public function handle(array $filters, string $schoolId): LengthAwarePaginator
    {
        $query = ClassGroup::query()
            ->forSchool($schoolId)
            ->with(['school']);

        $this->applySearchFilter($query, $filters);
        $this->applyLevelFilter($query, $filters);
        $this->applyAcademicYearFilter($query, $filters);
        $this->applyActiveFilter($query, $filters);
        $this->applySorting($query, $filters);

        $perPage = min($filters['per_page'] ?? 15, 100);

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    private function applySearchFilter(Builder $query, array $filters): void
    {
        if (empty($filters['q'])) {
            return;
        }

        $searchTerm = $filters['q'];
        $query->where(function (Builder $q) use ($searchTerm): void {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('level', 'LIKE', "%{$searchTerm}%")
                ->orWhere('section', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    private function applyLevelFilter(Builder $query, array $filters): void
    {
        if (empty($filters['level'])) {
            return;
        }

        $query->where('level', $filters['level']);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    private function applyAcademicYearFilter(Builder $query, array $filters): void
    {
        if (empty($filters['academic_year'])) {
            return;
        }

        $query->forAcademicYear($filters['academic_year']);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    private function applyActiveFilter(Builder $query, array $filters): void
    {
        if (! isset($filters['is_active'])) {
            return;
        }

        if ($filters['is_active']) {
            $query->active();
        } else {
            $query->where('is_active', false);
        }
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';

        if (in_array($sortBy, ['name', 'level', 'academic_year', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }
    }
}
