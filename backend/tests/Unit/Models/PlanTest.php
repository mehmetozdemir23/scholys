<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\School;

describe('Plan Model', function (): void {
    test('uses UUIDs for primary key', function (): void {
        $plan = Plan::factory()->create();

        expect($plan->id)->toBeString()
            ->and(mb_strlen($plan->id))->toBe(36)
            ->and($plan->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    test('can be created with factory', function (): void {
        $plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'price' => 29.99,
            'max_users' => 100,
            'features' => ['feature1', 'feature2'],
            'is_active' => true,
        ]);

        expect($plan->name)->toBe('Test Plan')
            ->and($plan->slug)->toBe('test-plan')
            ->and($plan->price)->toBe('29.99')
            ->and($plan->max_users)->toBe(100)
            ->and($plan->features)->toBe(['feature1', 'feature2'])
            ->and($plan->is_active)->toBeTrue()
            ->and($plan->exists)->toBeTrue();
    });

    describe('relationships', function () {
        test('has many schools', function (): void {
            $plan = Plan::factory()->create();
            $school1 = School::factory()->create(['plan_id' => $plan->id]);
            $school2 = School::factory()->create(['plan_id' => $plan->id]);

            expect($plan->schools)->toHaveCount(2)
                ->and($plan->schools->pluck('id'))->toContain($school1->id, $school2->id);
        });

        test('schools relationship returns collection of schools', function (): void {
            $plan = Plan::factory()->create();
            $school = School::factory()->create(['plan_id' => $plan->id]);

            expect($plan->schools->first())->toBeInstanceOf(School::class)
                ->and($plan->schools->first()->id)->toBe($school->id);
        });

        test('can have no schools', function (): void {
            $plan = Plan::factory()->create();

            expect($plan->schools)->toHaveCount(0)
                ->and($plan->schools)->toBeEmpty();
        });
    });

    describe('casts', function () {
        test('casts features to array', function (): void {
            $plan = Plan::factory()->create([
                'features' => ['feature1', 'feature2'],
            ]);

            expect($plan->features)->toBeArray()
                ->and($plan->features)->toBe(['feature1', 'feature2']);
        });

        test('casts is_active to boolean', function (): void {
            $plan = Plan::factory()->create(['is_active' => 1]);
            expect($plan->is_active)->toBeTrue();

            $plan = Plan::factory()->create(['is_active' => 0]);
            expect($plan->is_active)->toBeFalse();
        });

        test('casts price to decimal', function (): void {
            $plan = Plan::factory()->create(['price' => 29.99]);
            expect($plan->price)->toBe('29.99');
        });

        test('can handle null max_users', function (): void {
            $plan = Plan::factory()->create(['max_users' => null]);
            expect($plan->max_users)->toBeNull();
        });

        test('can handle null features', function (): void {
            $plan = Plan::factory()->create(['features' => null]);
            expect($plan->features)->toBeNull();
        });
    });

    describe('constants', function () {
        test('defines plan constants', function (): void {
            expect(Plan::FREE)->toBe('free')
                ->and(Plan::PREMIUM)->toBe('premium')
                ->and(Plan::ENTERPRISE)->toBe('enterprise');
        });
    });
});
