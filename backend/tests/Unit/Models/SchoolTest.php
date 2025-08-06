<?php

declare(strict_types=1);

use App\Models\School;
use App\Models\User;

describe('School Model', function (): void {
    test('uses UUIDs for primary key', function (): void {
        $school = School::factory()->create();

        expect($school->id)->toBeString()
            ->and(mb_strlen($school->id))->toBe(36)
            ->and($school->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    test('can be created with factory', function (): void {
        $school = School::factory()->create(['name' => 'Test School']);

        expect($school->name)->toBe('Test School')
            ->and($school->exists)->toBeTrue();
    });

    describe('relationships', function () {
        test('has many users', function (): void {
            $school = School::factory()->create();
            $user1 = User::factory()->create(['school_id' => $school->id]);
            $user2 = User::factory()->create(['school_id' => $school->id]);

            expect($school->users)->toHaveCount(2)
                ->and($school->users->pluck('id'))->toContain($user1->id, $user2->id);
        });

        test('users relationship returns collection of users', function (): void {
            $school = School::factory()->create();
            $user = User::factory()->create(['school_id' => $school->id]);

            expect($school->users->first())->toBeInstanceOf(User::class)
                ->and($school->users->first()->id)->toBe($user->id);
        });

        test('can have no users', function (): void {
            $school = School::factory()->create();

            expect($school->users)->toHaveCount(0)
                ->and($school->users)->toBeEmpty();
        });
    });
});
