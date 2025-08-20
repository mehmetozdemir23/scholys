<?php

declare(strict_types=1);

use App\Http\Requests\ImportUserRequest;
use App\Jobs\ImportUsers;
use App\Jobs\SendWelcomeEmail;
use App\Mail\ImportCompletedMail;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

describe('ImportUsers Job', function (): void {
    beforeEach(function (): void {
        Mail::fake();
        Queue::fake();

        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'super_admin']);
    });

    test('processes valid CSV data successfully', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\nJane,Smith,jane@example.com,staff";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(2)
            ->and($result['errorCount'])->toBe(0)
            ->and($result['errors'])->toBeEmpty();

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'school_id' => $school->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'school_id' => $school->id,
        ]);

        Queue::assertPushed(SendWelcomeEmail::class, 2);
        Mail::assertSent(ImportCompletedMail::class);
    });

    test('handles validation errors properly', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,,john@example.com,teacher\nJane,Smith,invalid-email,staff\n,Doe,valid@example.com,teacher";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(0)
            ->and($result['errorCount'])->toBe(3)
            ->and($result['errors'])->toHaveCount(3);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'valid@example.com']);

        Queue::assertNotPushed(SendWelcomeEmail::class);
        Mail::assertSent(ImportCompletedMail::class);
    });

    test('handles invalid role values', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,invalid_role\nJane,Smith,jane@example.com,teacher";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(1)
            ->and($result['errorCount'])->toBe(1);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    });

    test('handles duplicate email addresses', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);
        User::factory()->create(['email' => 'existing@example.com']);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,existing@example.com,teacher\nJane,Smith,jane@example.com,staff";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(1)
            ->and($result['errorCount'])->toBe(1);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    });

    test('skips empty lines in CSV', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\n\n\nJane,Smith,jane@example.com,staff\n\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(2)
            ->and($result['errorCount'])->toBe(0);
    });

    test('assigns roles correctly to imported users', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\nJohn,Doe,john@example.com,teacher\nJane,Smith,jane@example.com,staff";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $job->handle();

        $johnUser = User::where('email', 'john@example.com')->first();
        $janeUser = User::where('email', 'jane@example.com')->first();

        expect($johnUser->roles()->where('name', 'teacher')->exists())->toBeTrue()
            ->and($janeUser->roles()->where('name', 'staff')->exists())->toBeTrue();
    });

    test('uses batch insert for performance', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvLines = ['firstname,lastname,email,role'];
        for ($i = 1; $i <= 100; $i++) {
            $csvLines[] = "User{$i},Test{$i},user{$i}@example.com,teacher";
        }
        $csvContent = implode("\n", $csvLines);

        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);

        DB::enableQueryLog();
        $result = $job->handle();
        $queries = DB::getQueryLog();

        expect($result['successCount'])->toBe(100)
            ->and($result['errorCount'])->toBe(0);

        $insertQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'insert into `users`'));
        expect(count($insertQueries))->toBeLessThanOrEqual(2);
    });

    test('handles empty CSV file', function (): void {
        $school = School::factory()->create();
        $adminUser = User::factory()->create(['school_id' => $school->id]);

        $csvContent = "firstname,lastname,email,role\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $request = new ImportUserRequest();
        $request->files->set('users', $file);
        $request->setUserResolver(fn () => $adminUser);

        $job = new ImportUsers($request);
        $result = $job->handle();

        expect($result['successCount'])->toBe(0)
            ->and($result['errorCount'])->toBe(0)
            ->and($result['errors'])->toBeEmpty();

        Mail::assertSent(ImportCompletedMail::class);
    });
});
