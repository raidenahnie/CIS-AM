<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// API routes for dashboard
Route::prefix('api')->group(function () {
    Route::get('/user-stats/{userId?}', [DashboardController::class, 'getUserStats']);
    Route::get('/attendance-history/{userId?}', [DashboardController::class, 'getAttendanceHistory']);
    Route::get('/user-workplace/{userId?}', [DashboardController::class, 'getUserWorkplace']);
    Route::get('/current-status/{userId?}', [DashboardController::class, 'getCurrentStatus']);
    Route::post('/checkin', [DashboardController::class, 'checkIn']);
    Route::post('/perform-action', [DashboardController::class, 'performAction']);
    Route::post('/save-workplace', [DashboardController::class, 'saveWorkplace']);
});

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
