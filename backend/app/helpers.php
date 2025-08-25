<?php

declare(strict_types=1);

/**
 * Get the current academic year based on the current date.
 * Academic year starts in September.
 */
function getCurrentAcademicYear(): string
{
    $now = now();
    $currentYear = $now->year;

    if ($now->month < 9) {
        return ($currentYear - 1).'-'.$currentYear;
    }

    return $currentYear.'-'.($currentYear + 1);
}
