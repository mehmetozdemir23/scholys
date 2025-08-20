<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory, HasUuids;

    public const array ROLE_NAMES = [
        'super_admin' => 'Super Administrateur',
        'admin' => 'Administrateur',
        'teacher' => 'Enseignant',
        'staff' => 'Personnel',
    ];
}
