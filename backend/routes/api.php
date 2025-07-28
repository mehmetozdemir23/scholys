<?php

declare(strict_types=1);

use App\Http\Controllers\SchoolRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::post('/school/invite', [SchoolRegistrationController::class, 'sendInvitation'])
        ->name('school.invite');
    Route::get('/school/register', [SchoolRegistrationController::class, 'completeAccountSetup'])
        ->name('school.register');
});
