<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class SearchUsers
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function handle(array $filters, string $schoolId): LengthAwarePaginator
    {
        $query = User::query()
            ->where('school_id', $schoolId)
            ->with(['roles']);

        $this->applySearchFilter($query, $filters);
        $this->applyRoleFilter($query, $filters);
        $this->applySorting($query, $filters);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySearchFilter(Builder $query, array $filters): void
    {
        if (empty($filters['q'])) {
            return;
        }

        $searchTerm = $filters['q'];
        $query->where(function (Builder $q) use ($searchTerm): void {
            $q->whereAny(['firstname', 'lastname', 'email'], 'like', "%{$searchTerm}%");
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applyRoleFilter(Builder $query, array $filters): void
    {
        if (empty($filters['role'])) {
            return;
        }

        $query->whereHas('roles', function (Builder $q) use ($filters): void {
            $q->where('name', $filters['role']);
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);
    }
}
