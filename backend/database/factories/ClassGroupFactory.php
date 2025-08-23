<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassGroup>
 */
final class ClassGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levels = ['6e', '5e', '4e', '3e', '2de', '1re', 'Tle'];
        $sections = ['A', 'B', 'C', 'D'];
        $academicYears = ['2023-2024', '2024-2025', '2025-2026'];

        return [
            'id' => fake()->uuid(),
            'school_id' => School::factory(),
            'name' => fake()->randomElement([
                'Classe de 6e',
                'Classe de 5e',
                'Classe de 4e',
                'Classe de 3e',
                'Classe de 2de',
                'Classe de 1re',
                'Classe de Tle',
                'Mathématiques avancées',
                'Sciences expérimentales',
                'Littéraire',
                'Économique et social',
                'Sciences et Technologies',
                'Arts appliqués',
            ]).' - '.fake()->unique()->bothify('##??'),
            'level' => fake()->randomElement($levels),
            'section' => fake()->randomElement($sections),
            'description' => fake()->optional()->sentence(),
            'max_students' => fake()->optional()->numberBetween(15, 35),
            'academic_year' => fake()->randomElement($academicYears),
            'is_active' => fake()->boolean(80), // 80% chance d'être active
        ];
    }

    /**
     * Indicate that the class group is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the class group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific academic year.
     */
    public function forAcademicYear(string $academicYear): static
    {
        return $this->state(fn (array $attributes): array => [
            'academic_year' => $academicYear,
        ]);
    }

    /**
     * Set a specific level.
     */
    public function withLevel(string $level): static
    {
        return $this->state(fn (array $attributes): array => [
            'level' => $level,
        ]);
    }

    /**
     * Set a specific maximum number of students.
     */
    public function withMaxStudents(int $maxStudents): static
    {
        return $this->state(fn (array $attributes): array => [
            'max_students' => $maxStudents,
        ]);
    }

    /**
     * Create a class group with no student limit.
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes): array => [
            'max_students' => null,
        ]);
    }
}
