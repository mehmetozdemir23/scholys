<?php

declare(strict_types=1);

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
});
