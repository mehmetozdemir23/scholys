<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ClassGroup;

final class CreateClassGroup
{
    public function handle(array $attributes): ClassGroup
    {
        $classGroup = ClassGroup::create($attributes);
        $classGroup->load(['school']);

        return $classGroup;
    }
}
