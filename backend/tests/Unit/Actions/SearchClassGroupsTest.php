<?php

declare(strict_types=1);

use App\Actions\SearchClassGroups;
use App\Models\ClassGroup;
use App\Models\School;

describe('SearchClassGroups Action', function (): void {
    test('handles basic search without filters', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->count(3)->create(['school_id' => $school->id]);

        $action = new SearchClassGroups();
        $result = $action->handle([], $school->id);

        expect($result->total())->toBe(3);
    });

    test('applies search filter on name', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Mathématiques 6e',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Français 5e',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['q' => 'Math'], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->name)->toBe('Mathématiques 6e');
    });

    test('applies search filter on level', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '6e',
            'name' => 'Class A',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '5e',
            'name' => 'Class B',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['q' => '6e'], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->level)->toBe('6e');
    });

    test('applies search filter on section', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'section' => 'ALPHA',
            'name' => 'Class 1',
            'academic_year' => '2024-2025',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'section' => 'BETA',
            'name' => 'Class 2',
            'academic_year' => '2024-2025',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['q' => 'ALPHA'], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->section)->toBe('ALPHA');
    });

    test('applies level filter', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '6e',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '5e',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['level' => '6e'], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->level)->toBe('6e');
    });

    test('applies academic year filter', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'academic_year' => '2024-2025',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'academic_year' => '2023-2024',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['academic_year' => '2024-2025'], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->academic_year)->toBe('2024-2025');
    });

    test('applies active filter for true', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'is_active' => false,
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['is_active' => true], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->is_active)->toBeTrue();
    });

    test('applies active filter for false', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'is_active' => false,
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['is_active' => false], $school->id);

        expect($result->total())->toBe(1);
        expect($result->items()[0]->is_active)->toBeFalse();
    });

    test('applies valid sorting by name', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Zebra Class',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Alpha Class',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['sort_by' => 'name', 'sort_order' => 'desc'], $school->id);

        expect($result->items()[0]->name)->toBe('Zebra Class');
        expect($result->items()[1]->name)->toBe('Alpha Class');
    });

    test('applies valid sorting by level', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '6e',
            'name' => 'Class A',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'level' => '5e',
            'name' => 'Class B',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['sort_by' => 'level', 'sort_order' => 'desc'], $school->id);

        expect($result->items()[0]->level)->toBe('6e');
        expect($result->items()[1]->level)->toBe('5e');
    });

    test('applies valid sorting by academic year', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'academic_year' => '2024-2025',
            'name' => 'Class A',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'academic_year' => '2023-2024',
            'name' => 'Class B',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['sort_by' => 'academic_year', 'sort_order' => 'desc'], $school->id);

        expect($result->items()[0]->academic_year)->toBe('2024-2025');
        expect($result->items()[1]->academic_year)->toBe('2023-2024');
    });

    test('applies valid sorting by created_at', function (): void {
        $school = School::factory()->create();
        $old = ClassGroup::factory()->create([
            'school_id' => $school->id,
            'created_at' => now()->subDays(1),
        ]);
        $new = ClassGroup::factory()->create([
            'school_id' => $school->id,
            'created_at' => now(),
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['sort_by' => 'created_at', 'sort_order' => 'desc'], $school->id);

        expect($result->items()[0]->id)->toBe($new->id);
        expect($result->items()[1]->id)->toBe($old->id);
    });

    test('defaults to name sorting when invalid sort field provided', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Zebra Class',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Alpha Class',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle(['sort_by' => 'invalid_field'], $school->id);

        expect($result->items()[0]->name)->toBe('Alpha Class');
        expect($result->items()[1]->name)->toBe('Zebra Class');
    });

    test('respects per_page limit', function (): void {
        $school = School::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            ClassGroup::factory()->create([
                'school_id' => $school->id,
                'name' => "Class {$i}",
                'academic_year' => '2024-2025',
            ]);
        }

        $action = new SearchClassGroups();
        $result = $action->handle(['per_page' => 3], $school->id);

        expect($result->perPage())->toBe(3);
        expect($result->count())->toBe(3);
    });

    test('limits per_page to maximum of 100', function (): void {
        $school = School::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            ClassGroup::factory()->create([
                'school_id' => $school->id,
                'name' => "Test Class {$i}",
                'academic_year' => '2024-2025',
            ]);
        }

        $action = new SearchClassGroups();
        $result = $action->handle(['per_page' => 150], $school->id);

        expect($result->perPage())->toBe(100);
    });

    test('defaults to 15 per page when not specified', function (): void {
        $school = School::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            ClassGroup::factory()->create([
                'school_id' => $school->id,
                'name' => "Default Class {$i}",
                'academic_year' => '2024-2025',
            ]);
        }

        $action = new SearchClassGroups();
        $result = $action->handle([], $school->id);

        expect($result->perPage())->toBe(15);
    });

    test('includes school relationship', function (): void {
        $school = School::factory()->create();
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Relationship Test Class',
            'academic_year' => '2024-2025',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle([], $school->id);

        expect($result->items()[0]->relationLoaded('school'))->toBeTrue();
    });

    test('ignores empty filters', function (): void {
        $school = School::factory()->create();

        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Filter Test Class 1',
            'academic_year' => '2024-2025',
        ]);
        ClassGroup::factory()->create([
            'school_id' => $school->id,
            'name' => 'Filter Test Class 2',
            'academic_year' => '2024-2025',
        ]);

        $action = new SearchClassGroups();
        $result = $action->handle([
            'q' => '',
            'level' => '',
            'academic_year' => '',
        ], $school->id);

        expect($result->total())->toBe(2);
    });
});
