<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @use HasFactory<\Database\Factories\ClassGroupFactory>
 */
final class ClassGroup extends Model
{
    /** @use HasFactory<\Database\Factories\ClassGroupFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the school that the class group belongs to.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the students (users with student role) in this class group.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('assigned_at')
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'student');
            });
    }

    /**
     * Get all users in this class group (including teachers).
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    /**
     * Get the teachers assigned to this class group.
     *
     * @return BelongsToMany<User, $this>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('assigned_at')
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'teacher');
            });
    }

    /**
     * Scope a query to only include active classes.
     *
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by school.
     *
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeForSchool(Builder $query, string $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope a query to filter by academic year.
     *
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeForAcademicYear(Builder $query, string $academicYear): Builder
    {
        return $query->where('academic_year', $academicYear);
    }

    /**
     * Get the current student count.
     */
    public function getCurrentStudentCount(): int
    {
        return $this->students()->count();
    }

    /**
     * Check if the class group is full.
     */
    public function isFull(): bool
    {
        if ($this->max_students === null) {
            return false;
        }

        return $this->getCurrentStudentCount() >= $this->max_students;
    }

    /**
     * Get available spots in the class group.
     */
    public function getAvailableSpots(): ?int
    {
        if ($this->max_students === null) {
            return null;
        }

        return max(0, $this->max_students - $this->getCurrentStudentCount());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }
}
