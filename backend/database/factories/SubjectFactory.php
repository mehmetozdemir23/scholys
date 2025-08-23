<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->randomElement([
                'Mathématiques',
                'Français',
                'Histoire-Géographie',
                'Sciences Physiques',
                'Sciences de la Vie et de la Terre',
                'Anglais',
                'Espagnol',
                'Education Physique et Sportive',
                'Arts Plastiques',
                'Musique',
                'Philosophie',
                'Économie',
                'Technologie',
            ]),
        ];
    }
}
