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
    Route::get('/user-workplaces/{userId?}', [DashboardController::class, 'getUserWorkplaces']);
    Route::get('/current-status/{userId?}', [DashboardController::class, 'getCurrentStatus']);
    Route::post('/checkin', [DashboardController::class, 'checkIn']);
    Route::post('/perform-action', [DashboardController::class, 'performAction']);
    Route::post('/save-workplace', [DashboardController::class, 'saveWorkplace']);
    Route::post('/set-primary-workplace', [DashboardController::class, 'setPrimaryWorkplace']);
});

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Admin only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    
    // Workplace CRUD
    Route::post('/workplaces', [App\Http\Controllers\AdminController::class, 'storeWorkplace']);
    Route::put('/workplaces/{workplace}', [App\Http\Controllers\AdminController::class, 'updateWorkplace']);
    Route::delete('/workplaces/{workplace}', [App\Http\Controllers\AdminController::class, 'deleteWorkplace']);
    
    // User assignments
    Route::post('/assign-workplace', [App\Http\Controllers\AdminController::class, 'assignWorkplace']);
    Route::delete('/remove-assignment', [App\Http\Controllers\AdminController::class, 'removeWorkplaceAssignment']);
});
