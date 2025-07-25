<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')->group(function (): void {
    Route::post('/school/invite', [App\Http\Controllers\SchoolRegistrationController::class, 'sendInvitation'])
        ->name('school.invite');
    Route::get('/school/confirm/{token}', [App\Http\Controllers\SchoolRegistrationController::class, 'confirmInvitation'])
        ->name('school.confirm');
});
