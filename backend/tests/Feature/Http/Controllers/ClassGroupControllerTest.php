<?php

declare(strict_types=1);

use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\School;
use App\Models\User;

require_once __DIR__.'/../../../Helpers/TestHelpers.php';

describe('ClassGroupController', function (): void {
    describe('index', function (): void {
        test('authenticated user can list class groups for their school', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Classe A',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $otherSchool->id,
                'name' => 'Classe B',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['name'])->toBe('Classe A');
        });

        test('can filter class groups by search term', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Mathématiques 6e',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Français 5e',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?q=Math');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['name'])->toBe('Mathématiques 6e');
        });

        test('can filter class groups by level', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'level' => '6e',
                'name' => 'Classe 6e A',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'level' => '5e',
                'name' => 'Classe 5e A',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?level=6e');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['level'])->toBe('6e');
        });

        test('can filter class groups by academic year', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'academic_year' => '2024-2025',
                'name' => 'Classe A 2024',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'academic_year' => '2023-2024',
                'name' => 'Classe A 2023',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?academic_year=2024-2025');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['academic_year'])->toBe('2024-2025');
        });

        test('can filter class groups by active status true', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => true,
                'name' => 'Active Class',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => false,
                'name' => 'Inactive Class',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?is_active=1');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['name'])->toBe('Active Class');
        });

        test('can filter class groups by active status false', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => true,
                'name' => 'Active Class',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => false,
                'name' => 'Inactive Class',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?is_active=0');

            $response->assertOk();
            $data = $response->json();

            expect($data['data'])->toHaveCount(1);
            expect($data['data'][0]['name'])->toBe('Inactive Class');
        });

        test('validates invalid sort field', function (): void {
            $admin = createSuperAdmin();

            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Zebra Class',
                'academic_year' => '2024-2025',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?sort_by=invalid_field&sort_order=desc');

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['sort_by']);
        });
    });

    describe('store', function (): void {
        test('admin can create a class group successfully', function (): void {
            $admin = createSuperAdmin();

            $classGroupData = [
                'name' => 'Classe de 6e A',
                'level' => '6e',
                'section' => 'A',
                'description' => 'Classe de sixième section A',
                'max_students' => 30,
                'academic_year' => '2024-2025',
                'is_active' => true,
            ];

            $response = $this->actingAs($admin)
                ->postJson('/api/class-groups', $classGroupData);

            $response->assertCreated()
                ->assertJson(['message' => 'Classe créée avec succès!']);

            $this->assertDatabaseHas('class_groups', [
                'name' => 'Classe de 6e A',
                'level' => '6e',
                'section' => 'A',
                'school_id' => $admin->school_id,
            ]);
        });

        test('class group name is required', function (): void {
            $admin = createSuperAdmin();

            $response = $this->actingAs($admin)
                ->postJson('/api/class-groups', [
                    'academic_year' => '2024-2025',
                ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        test('class group uses default values when not provided', function (): void {
            $admin = createSuperAdmin();

            $response = $this->actingAs($admin)
                ->postJson('/api/class-groups', [
                    'name' => 'Test Class',
                ]);

            $response->assertCreated();

            $classGroup = ClassGroup::where('name', 'Test Class')->first();
            expect($classGroup->is_active)->toBe(true)
                ->and($classGroup->academic_year)->toMatch('/^\d{4}-\d{4}$/')
                ->and($classGroup->academic_year)->not->toBeNull();
        });
    });

    describe('show', function (): void {
        test('admin can view a class group from their school', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Test Class',
                'max_students' => 25,
            ]);

            $response = $this->actingAs($admin)
                ->getJson("/api/class-groups/{$classGroup->id}");

            $response->assertOk()
                ->assertJsonStructure([
                    'class_group' => [
                        'id',
                        'name',
                        'level',
                        'school',
                        'students',
                        'teachers',
                    ],
                    'stats' => [
                        'student_count',
                        'available_spots',
                        'is_full',
                    ],
                ]);
        });
    });

    describe('update', function (): void {
        test('admin can update a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'name' => 'Original Name',
            ]);

            $response = $this->actingAs($admin)
                ->patchJson("/api/class-groups/{$classGroup->id}", [
                    'name' => 'Updated Name',
                    'level' => '5e',
                ]);

            $response->assertOk()
                ->assertJson(['message' => 'Classe modifiée avec succès!']);

            $this->assertDatabaseHas('class_groups', [
                'id' => $classGroup->id,
                'name' => 'Updated Name',
                'level' => '5e',
            ]);
        });
    });

    describe('destroy', function (): void {
        test('admin can delete a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);

            $response = $this->actingAs($admin)
                ->deleteJson("/api/class-groups/{$classGroup->id}");

            $response->assertOk()
                ->assertJson(['message' => 'Classe supprimée avec succès!']);

            $this->assertDatabaseMissing('class_groups', [
                'id' => $classGroup->id,
            ]);
        });
    });

    describe('assignStudent', function (): void {
        test('admin can assign a student to a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'max_students' => 30,
            ]);
            $student = createStudent($admin->school_id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}");

            $response->assertOk()
                ->assertJson(['message' => 'Élève affecté à la classe avec succès!']);

            $this->assertDatabaseHas('class_group_user', [
                'class_group_id' => $classGroup->id,
                'user_id' => $student->id,
            ]);
        });

        test('cannot assign student when class is full', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'max_students' => 1,
            ]);
            $student1 = createStudent($admin->school_id);
            $student2 = createStudent($admin->school_id);

            $classGroup->students()->attach($student1->id, ['assigned_at' => now()]);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/students/{$student2->id}");

            $response->assertUnprocessable()
                ->assertJson(['message' => 'La classe est complète.']);
        });

        test('cannot assign non-student user', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($admin->school_id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/students/{$teacher->id}");

            $response->assertUnprocessable()
                ->assertJson(['message' => 'L\'utilisateur doit avoir le rôle élève.']);
        });

        test('cannot assign student from different school', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $student = createStudent($otherSchool->id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}");

            $response->assertNotFound()
                ->assertJson(['message' => 'Utilisateur non trouvé.']);
        });

        test('cannot assign student already in class', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $student = createStudent($admin->school_id);

            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/students/{$student->id}");

            $response->assertUnprocessable()
                ->assertJson(['message' => 'L\'élève est déjà dans cette classe.']);
        });
    });

    describe('removeStudent', function (): void {
        test('admin can remove a student from a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $student = createStudent($admin->school_id);

            $classGroup->students()->attach($student->id, ['assigned_at' => now()]);

            $response = $this->actingAs($admin)
                ->deleteJson("/api/class-groups/{$classGroup->id}/students/{$student->id}");

            $response->assertOk()
                ->assertJson(['message' => 'Élève retiré de la classe avec succès!']);

            $this->assertDatabaseMissing('class_group_user', [
                'class_group_id' => $classGroup->id,
                'user_id' => $student->id,
            ]);
        });

        test('admin can remove student from different school safely', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $student = createStudent($otherSchool->id);

            $response = $this->actingAs($admin)
                ->deleteJson("/api/class-groups/{$classGroup->id}/students/{$student->id}");

            $response->assertNotFound()
                ->assertJson(['message' => 'Utilisateur non trouvé.']);
        });
    });

    describe('assignTeacher', function (): void {
        test('admin can assign a teacher to a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($admin->school_id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/teachers/{$teacher->id}");

            $response->assertOk()
                ->assertJson(['message' => 'Enseignant assigné à la classe avec succès!']);

            $this->assertDatabaseHas('class_group_user', [
                'class_group_id' => $classGroup->id,
                'user_id' => $teacher->id,
            ]);
        });

        test('cannot assign non-teacher user', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $student = createStudent($admin->school_id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/teachers/{$student->id}");

            $response->assertUnprocessable()
                ->assertJson(['message' => 'L\'utilisateur doit avoir le rôle enseignant.']);
        });

        test('cannot assign teacher from different school', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($otherSchool->id);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/teachers/{$teacher->id}");

            $response->assertNotFound()
                ->assertJson(['message' => 'Utilisateur non trouvé.']);
        });

        test('cannot assign teacher already in class', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($admin->school_id);

            $classGroup->teachers()->attach($teacher->id, ['assigned_at' => now()]);

            $response = $this->actingAs($admin)
                ->postJson("/api/class-groups/{$classGroup->id}/teachers/{$teacher->id}");

            $response->assertUnprocessable()
                ->assertJson(['message' => 'L\'enseignant est déjà assigné à cette classe.']);
        });
    });

    describe('removeTeacher', function (): void {
        test('admin can remove a teacher from a class group', function (): void {
            $admin = createSuperAdmin();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($admin->school_id);

            $classGroup->teachers()->attach($teacher->id, ['assigned_at' => now()]);

            $response = $this->actingAs($admin)
                ->deleteJson("/api/class-groups/{$classGroup->id}/teachers/{$teacher->id}");

            $response->assertOk()
                ->assertJson(['message' => 'Enseignant retiré de la classe avec succès!']);

            $this->assertDatabaseMissing('class_group_user', [
                'class_group_id' => $classGroup->id,
                'user_id' => $teacher->id,
            ]);
        });

        test('admin can remove teacher from different school safely', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
            ]);
            $teacher = createTeacher($otherSchool->id);

            $response = $this->actingAs($admin)
                ->deleteJson("/api/class-groups/{$classGroup->id}/teachers/{$teacher->id}");

            $response->assertNotFound()
                ->assertJson(['message' => 'Utilisateur non trouvé.']);
        });
    });

    describe('stats', function (): void {
        test('admin can view class group statistics', function (): void {
            $admin = createSuperAdmin();
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => true,
                'level' => '6e',
            ]);
            ClassGroup::factory()->create([
                'school_id' => $admin->school_id,
                'is_active' => false,
                'level' => '5e',
            ]);

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups/stats');

            $response->assertOk()
                ->assertJsonStructure([
                    'total',
                    'active',
                    'inactive',
                    'by_level',
                ]);

            $data = $response->json();
            expect($data['total'])->toBe(2);
            expect($data['active'])->toBe(1);
            expect($data['inactive'])->toBe(1);
        });
    });

    describe('authorization', function (): void {
        test('unauthenticated user cannot access class groups', function (): void {
            $response = $this->getJson('/api/class-groups');

            $response->assertUnauthorized();
        });

        test('student cannot create class groups', function (): void {
            $school = School::factory()->create();
            $studentRole = Role::firstOrCreate(['name' => 'student']);
            $student = User::factory()->create(['school_id' => $school->id]);
            $student->roles()->attach($studentRole);

            $response = $this->actingAs($student)
                ->postJson('/api/class-groups', [
                    'name' => 'Test Class',
                    'academic_year' => '2024-2025',
                ]);

            $response->assertForbidden();
        });

        test('teacher cannot update class groups', function (): void {
            $school = School::factory()->create();
            $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
            $teacher = User::factory()->create(['school_id' => $school->id]);
            $teacher->roles()->attach($teacherRole);
            $classGroup = ClassGroup::factory()->create(['school_id' => $school->id]);

            $response = $this->actingAs($teacher)
                ->patchJson("/api/class-groups/{$classGroup->id}", [
                    'name' => 'Updated Name',
                ]);

            $response->assertForbidden();
        });

        test('admin cannot access class groups from different school', function (): void {
            $admin = createSuperAdmin();
            $otherSchool = School::factory()->create();
            $classGroup = ClassGroup::factory()->create(['school_id' => $otherSchool->id]);

            $response = $this->actingAs($admin)
                ->getJson("/api/class-groups/{$classGroup->id}");

            $response->assertForbidden();
        });
    });

    describe('validation errors', function (): void {
        test('class group name cannot be empty', function (): void {
            $admin = createSuperAdmin();

            $response = $this->actingAs($admin)
                ->postJson('/api/class-groups', [
                    'name' => '',
                    'academic_year' => '2024-2025',
                ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        test('max_students must be positive integer', function (): void {
            $admin = createSuperAdmin();

            $response = $this->actingAs($admin)
                ->postJson('/api/class-groups', [
                    'name' => 'Test Class',
                    'academic_year' => '2024-2025',
                    'max_students' => -5,
                ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['max_students']);
        });
    });

    describe('edge cases', function (): void {
        test('handles non-existent class group gracefully', function (): void {
            $admin = createSuperAdmin();
            $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';

            $response = $this->actingAs($admin)
                ->getJson("/api/class-groups/{$nonExistentId}");

            $response->assertNotFound();
        });

        test('handles invalid UUID format', function (): void {
            $admin = createSuperAdmin();

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups/invalid-id');

            $response->assertNotFound();
        });

        test('can handle pagination with large page numbers', function (): void {
            $admin = createSuperAdmin();

            for ($i = 1; $i <= 5; $i++) {
                ClassGroup::factory()->create([
                    'school_id' => $admin->school_id,
                    'name' => "Test Class {$i}",
                    'academic_year' => '2024-2025',
                ]);
            }

            $response = $this->actingAs($admin)
                ->getJson('/api/class-groups?page=100');

            $response->assertOk();
            $data = $response->json();
            expect($data['data'])->toBeEmpty();
        });
    });
});
