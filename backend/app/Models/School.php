<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class School extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use HasFactory, HasUuids;

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<User, $this> */
    public function students(): HasMany
    {
        return $this->users()->whereHas('roles', function (Builder $query): void {
            $query->where('name', 'student');
        });
    }

    /** @return HasMany<User, $this> */
    public function teachers(): HasMany
    {
        return $this->users()->whereHas('roles', function (Builder $query): void {
            $query->where('name', 'teacher');
        });
    }

    /** @return HasMany<ClassGroup, $this> */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class);
    }
}
