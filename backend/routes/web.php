<?php

declare(strict_types=1);
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LogoutController;

Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth');
