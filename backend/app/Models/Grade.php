<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Grade extends Model
{
    /** @use HasFactory<\Database\Factories\GradeFactory> */
    use HasFactory, HasUuids;

    protected $attributes = [
        'is_active' => true,
        'coefficient' => 1.0,
        'max_value' => 20.0,
    ];

    /** @return BelongsTo<ClassGroup, $this> */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** @return BelongsTo<User, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        self::creating(function (Grade $grade): void {
            $grade->given_at ??= now();
            $grade->academic_year ??= getCurrentAcademicYear();
        });
    }

    protected function casts(): array
    {
        return [
            'given_at' => 'date',
            'value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }
}
