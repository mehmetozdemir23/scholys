<?php

declare(strict_types=1);

use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ImportUserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolRegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::name('school.')->prefix('/school')->group(function (): void {
        Route::post('invite', [SchoolRegistrationController::class, 'sendInvitation'])
            ->name('invite');
        Route::get('register', [SchoolRegistrationController::class, 'completeAccountSetup'])
            ->name('register');
    });
});

Route::middleware('auth:sanctum')->group(function (): void {

    Route::name('users.')->prefix('/users')->group(function (): void {
        Route::get('search', [UserController::class, 'search'])->name('search');
        Route::post('', [UserController::class, 'store'])->name('store');
        Route::patch('{user}', [UserController::class, 'update'])->name('update');
        Route::post('import', ImportUserController::class);
    });

    Route::post('user/password', [UserController::class, 'updatePassword'])
        ->name('user.password.update');

    Route::name('school.')->prefix('/school')->group(function (): void {
        Route::post('registration/reset-password', [SchoolRegistrationController::class, 'resetPasswordAfterInvitation'])
            ->name('registration.reset-password');
        Route::patch('{school}', [SchoolController::class, 'update'])
            ->name('update');
    });

    Route::name('class-groups.')->prefix('/class-groups')->group(function (): void {
        Route::get('', [ClassGroupController::class, 'index'])->name('index');
        Route::post('', [ClassGroupController::class, 'store'])->name('store');
        Route::get('stats', [ClassGroupController::class, 'stats'])->name('stats');
        Route::get('{classGroup}', [ClassGroupController::class, 'show'])->name('show');
        Route::patch('{classGroup}', [ClassGroupController::class, 'update'])->name('update');
        Route::delete('{classGroup}', [ClassGroupController::class, 'destroy'])->name('destroy');
        Route::post('{classGroup}/students/{student}/subjects/{subject}/notes', [GradeController::class, 'store'])->name('students.grades.store');

        Route::name('students.')->prefix('{classGroup}/students')->group(function (): void {
            Route::post('{user}', [ClassGroupController::class, 'assignStudent'])->name('assign');
            Route::delete('{user}', [ClassGroupController::class, 'removeStudent'])->name('remove');
        });

        Route::name('teachers.')->prefix('{classGroup}/teachers')->group(function (): void {
            Route::post('{user}', [ClassGroupController::class, 'assignTeacher'])->name('assign');
            Route::delete('{user}', [ClassGroupController::class, 'removeTeacher'])->name('remove');
        });
    });
});
