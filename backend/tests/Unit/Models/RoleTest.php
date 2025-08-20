<?php

declare(strict_types=1);

use App\Models\Role;

describe('Role Model', function (): void {
    test('has super admin constant', function (): void {
        expect('super_admin')->toBe('super_admin');
    });

    test('uses UUIDs for primary key', function (): void {
        $role = Role::factory()->create();

        expect($role->id)->toBeString()
            ->and(mb_strlen($role->id))->toBe(36)
            ->and($role->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    test('can be created with factory', function (): void {
        $role = Role::factory()->create(['name' => 'test_role']);

        expect($role->name)->toBe('test_role')
            ->and($role->exists)->toBeTrue();
    });

    test('can create super admin role', function (): void {
        $role = Role::create(['name' => 'super_admin']);

        expect($role->name)->toBe('super_admin')
            ->and($role->exists)->toBeTrue();
    });
});
