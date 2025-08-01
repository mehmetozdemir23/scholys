<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\Role;
use App\Models\School;
use App\Models\User;

describe('PlanController', function (): void {
    beforeEach(function (): void {
        Role::create(['name' => Role::SUPER_ADMIN]);
        $this->school = School::factory()->create();
        $this->user = User::factory()->create(['school_id' => $this->school->id]);
        $this->user->assignRole(Role::SUPER_ADMIN);
    });

    describe('index', function (): void {
        test('returns all active plans', function (): void {
            $activePlan1 = Plan::factory()->create(['is_active' => true]);
            $activePlan2 = Plan::factory()->create(['is_active' => true]);
            $inactivePlan = Plan::factory()->create(['is_active' => false]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/plans');

            $response->assertStatus(200)
                ->assertJsonCount(2)
                ->assertJsonFragment(['id' => $activePlan1->id])
                ->assertJsonFragment(['id' => $activePlan2->id])
                ->assertJsonMissing(['id' => $inactivePlan->id]);
        });

        test('returns empty array when no active plans exist', function (): void {
            Plan::factory()->create(['is_active' => false]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/plans');

            $response->assertStatus(200)
                ->assertJson([]);
        });

        test('includes all plan attributes', function (): void {
            $plan = Plan::factory()->create([
                'name' => 'Premium Plan',
                'slug' => 'premium',
                'price' => 29.99,
                'max_users' => 100,
                'features' => ['feature1', 'feature2'],
                'is_active' => true,
            ]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/plans');

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $plan->id,
                    'name' => 'Premium Plan',
                    'slug' => 'premium',
                    'price' => '29.99',
                    'max_users' => 100,
                    'features' => ['feature1', 'feature2'],
                    'is_active' => true,
                ]);
        });

        test('requires authentication', function (): void {
            $response = $this->getJson('/api/plans');

            $response->assertStatus(401);
        });
    });
});
