<?php

declare(strict_types=1);

use Carbon\Carbon;

test('getCurrentAcademicYear returns current year format before September', function (): void {
    Carbon::setTestNow('2024-06-15');

    expect(getCurrentAcademicYear())->toBe('2023-2024');
});

test('getCurrentAcademicYear returns next year format after September', function (): void {
    Carbon::setTestNow('2024-10-15');

    expect(getCurrentAcademicYear())->toBe('2024-2025');
});

test('getCurrentAcademicYear returns next year format in September', function (): void {
    Carbon::setTestNow('2024-09-15');

    expect(getCurrentAcademicYear())->toBe('2024-2025');
});

test('getCurrentAcademicYear follows correct format', function (): void {
    $academicYear = getCurrentAcademicYear();

    expect($academicYear)->toMatch('/^\d{4}-\d{4}$/');
});
