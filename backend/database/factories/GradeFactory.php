<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'teacher_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'class_group_id' => ClassGroup::factory(),
            'value' => fake()->randomFloat(2, 0, 20),
            'max_value' => 20.00,
            'coefficient' => fake()->randomFloat(2, 0.5, 3),
            'title' => fake()->optional()->sentence(3),
            'comment' => fake()->optional()->text(100),
            'given_at' => fake()->date(),
            'academic_year' => fake()->randomElement(['2023-2024', '2024-2025', '2025-2026']),
            'is_active' => true,
            'deactivated_at' => null,
        ];
    }
}
