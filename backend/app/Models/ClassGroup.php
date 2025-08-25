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

    protected $attributes = [
        'is_active' => true,
    ];

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('assigned_at')
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'student');
            });
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    /** @return BelongsToMany<User, $this> */
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
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeForSchool(Builder $query, string $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     * @return Builder<ClassGroup>
     */
    public function scopeForAcademicYear(Builder $query, string $academicYear): Builder
    {
        return $query->where('academic_year', $academicYear);
    }

    public function getCurrentStudentCount(): int
    {
        return $this->students()->count();
    }

    public function isFull(): bool
    {
        if ($this->max_students === null) {
            return false;
        }

        return $this->getCurrentStudentCount() >= $this->max_students;
    }

    public function getAvailableSpots(): ?int
    {
        if ($this->max_students === null) {
            return null;
        }

        return max(0, $this->max_students - $this->getCurrentStudentCount());
    }

    protected static function booted(): void
    {
        self::creating(function (ClassGroup $classGroup): void {
            $classGroup->academic_year ??= getCurrentAcademicYear();
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }
}
