<?php

declare(strict_types=1);

use App\Models\ClassGroup;

require_once __DIR__.'/../../../Helpers/TestHelpers.php';

test('super admin can access school stats', function () {
    $superAdmin = createSuperAdmin();
    $school = $superAdmin->school;

    createTeacher($school->id);
    createStudent($school->id);
    ClassGroup::factory()->create(['school_id' => $school->id]);

    $response = $this->actingAs($superAdmin)
        ->getJson('/api/dashboard/school-stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total_students',
            'total_classes',
            'total_teachers',
            'school_average',
        ]);
});

test('teacher can access school stats', function () {
    $teacher = createTeacher();

    $response = $this->actingAs($teacher)
        ->getJson('/api/dashboard/school-stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total_students',
            'total_classes',
            'total_teachers',
            'school_average',
        ]);
});

test('student cannot access school stats', function () {
    $student = createStudent();

    $response = $this->actingAs($student)
        ->getJson('/api/dashboard/school-stats');

    $response->assertStatus(403);
});

test('unauthenticated user cannot access school stats', function () {
    $response = $this->getJson('/api/dashboard/school-stats');

    $response->assertStatus(401);
});

test('school stats returns correct counts', function () {
    $superAdmin = createSuperAdmin();
    $school = $superAdmin->school;

    createTeacher($school->id);
    createTeacher($school->id);
    createStudent($school->id);
    createStudent($school->id);
    createStudent($school->id);

    ClassGroup::factory()->count(2)->create([
        'school_id' => $school->id,
        'academic_year' => getCurrentAcademicYear(),
    ]);

    ClassGroup::factory()->create([
        'school_id' => $school->id,
        'academic_year' => '2022-2023',
    ]);

    $response = $this->actingAs($superAdmin)
        ->getJson('/api/dashboard/school-stats');

    $response->assertStatus(200)
        ->assertJson([
            'total_students' => 3,
            'total_teachers' => 2,
            'total_classes' => 2,
            'school_average' => 0.0,
        ]);
});

test('school stats only counts users from same school', function () {
    $superAdmin = createSuperAdmin();
    $otherSchool = createSuperAdmin()->school;

    createStudent($superAdmin->school->id);
    createStudent($otherSchool->id);
    createTeacher($superAdmin->school->id);

    ClassGroup::factory()->create([
        'school_id' => $superAdmin->school->id,
        'academic_year' => getCurrentAcademicYear(),
    ]);

    $response = $this->actingAs($superAdmin)
        ->getJson('/api/dashboard/school-stats');

    $response->assertStatus(200)
        ->assertJson([
            'total_students' => 1,
            'total_teachers' => 1,
            'total_classes' => 1,
            'school_average' => 0.0,
        ]);
});
