<?php

declare(strict_types=1);

use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolRegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::post('/school/invite', [SchoolRegistrationController::class, 'sendInvitation'])
        ->name('school.invite');
    Route::get('/school/register', [SchoolRegistrationController::class, 'completeAccountSetup'])
        ->name('school.register');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/user/password', [UserController::class, 'updatePassword'])
        ->name('user.password.update');
    Route::post('/school/registration/reset-password', [SchoolRegistrationController::class, 'resetPasswordAfterInvitation'])
        ->name('school.registration.reset-password');
    Route::patch('/school/{school}', [SchoolController::class, 'update'])
        ->name('school.update');
});
