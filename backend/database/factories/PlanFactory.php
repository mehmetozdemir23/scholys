<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
final class PlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(),
            'price' => $this->faker->randomFloat(2, 0, 999),
            'max_users' => $this->faker->numberBetween(1, 1000),
            'features' => $this->faker->randomElements(
                ['feature1', 'feature2', 'feature3', 'feature4'],
                $this->faker->numberBetween(1, 4)
            ),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a free plan.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Free',
            'slug' => Plan::FREE,
            'price' => 0,
            'max_users' => 10,
            'features' => ['basic_features'],
        ]);
    }

    /**
     * Create a premium plan.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Premium',
            'slug' => Plan::PREMIUM,
            'price' => 29.99,
            'max_users' => 100,
            'features' => ['basic_features', 'premium_features'],
        ]);
    }

    /**
     * Create an enterprise plan.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Enterprise',
            'slug' => Plan::ENTERPRISE,
            'price' => 99.99,
            'max_users' => null,
            'features' => ['basic_features', 'premium_features', 'enterprise_features'],
        ]);
    }
}
