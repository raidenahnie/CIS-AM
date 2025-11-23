<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AbsenceRequestController;
use App\Http\Controllers\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('landing');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Registration disabled - users are created by admin only
// Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
// Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Password reset routes
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// API routes for dashboard - Protected by authentication and user authorization
Route::prefix('api')->middleware(['auth', 'authorize.user'])->group(function () {
    Route::get('/user-stats/{userId}', [DashboardController::class, 'getUserStats']);
    Route::get('/attendance-history/{userId}', [DashboardController::class, 'getAttendanceHistory']);
    Route::get('/attendance-logs/{userId}', [DashboardController::class, 'getAttendanceLogs']);
    Route::get('/user-workplace/{userId}', [DashboardController::class, 'getUserWorkplace']);
    Route::get('/user-workplaces/{userId}', [DashboardController::class, 'getUserWorkplaces']);
    Route::get('/all-workplaces', [DashboardController::class, 'getAllWorkplaces']);
    Route::get('/user/workplaces', [DashboardController::class, 'getCurrentUserWorkplaces']);
    Route::get('/current-status/{userId}', [DashboardController::class, 'getCurrentStatus']);
    Route::post('/checkin', [DashboardController::class, 'checkIn']);
    Route::post('/perform-action', [DashboardController::class, 'performAction']);
    Route::post('/save-workplace', [DashboardController::class, 'saveWorkplace']);
    Route::post('/set-primary-workplace', [DashboardController::class, 'setPrimaryWorkplace']);
    Route::get('/manual-entry-code', function() {
        $code = \App\Models\SystemSetting::get('manual_entry_code', 'DEPED2025');
        return response()->json(['code' => $code]);
    });
    Route::get('/special-checkin-logs/{userId}', [DashboardController::class, 'getSpecialCheckinLogs']);
    Route::post('/special-checkin', [DashboardController::class, 'specialCheckin']);
    Route::get('/today-checkin-type/{userId}', [DashboardController::class, 'getTodayCheckinType']);
    
    // Absence records endpoints
    Route::get('/absence-records/{userId}', [DashboardController::class, 'getAbsenceRecords']);
    Route::get('/weekly-absence-summary/{userId}', [DashboardController::class, 'getWeeklyAbsenceSummary']);
    Route::get('/monthly-absence-summary/{userId}', [DashboardController::class, 'getMonthlyAbsenceSummary']);
    
    // Absence request endpoints
    Route::get('/absence-requests', [AbsenceRequestController::class, 'index']);
    Route::post('/absence-requests', [AbsenceRequestController::class, 'store']);
    Route::delete('/absence-requests/{id}', [AbsenceRequestController::class, 'destroy']);
    Route::get('/absence-requests/pending-count', [AbsenceRequestController::class, 'getPendingCount']);
});

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
        // to enable maintenance mode view, replace above line with below line
        // return view('error.maintenance');
    })->name('dashboard');
});

// Admin only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    
    // List endpoints for dropdowns
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'getUsers']);
    Route::get('/workplaces', [App\Http\Controllers\AdminController::class, 'getWorkplaces']);
    
    // Workplace CRUD
    Route::get('/workplaces/{workplace}', [App\Http\Controllers\AdminController::class, 'getWorkplace']);
    Route::post('/workplaces', [App\Http\Controllers\AdminController::class, 'storeWorkplace']);
    Route::put('/workplaces/{workplace}', [App\Http\Controllers\AdminController::class, 'updateWorkplace']);
    Route::delete('/workplaces/{workplace}', [App\Http\Controllers\AdminController::class, 'deleteWorkplace']);
    
    // User CRUD
    Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'getUser']);
    Route::post('/users', [App\Http\Controllers\AdminController::class, 'storeUser']);
    Route::put('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser']);
    
    // User-Workplace assignments
    Route::post('/assign-workplace', [App\Http\Controllers\AdminController::class, 'assignWorkplace']);
    Route::delete('/remove-assignment', [App\Http\Controllers\AdminController::class, 'removeWorkplaceAssignment']);
    Route::get('/user-workplaces/{user}', [App\Http\Controllers\AdminController::class, 'getUserWorkplaces']);
    Route::get('/workplace-users/{workplace}', [App\Http\Controllers\AdminController::class, 'getWorkplaceUsers']);
    Route::post('/set-primary-workplace', [App\Http\Controllers\AdminController::class, 'setPrimaryWorkplace']);
    Route::put('/update-user-role', [App\Http\Controllers\AdminController::class, 'updateUserWorkplaceRole']);
    
    // Employee location tracking
    Route::get('/employee-locations', [App\Http\Controllers\AdminController::class, 'getEmployeeLocations']);
    Route::get('/user-location-details/{user}', [App\Http\Controllers\AdminController::class, 'getUserLocationDetails']);
    
    // Bulk operations
    Route::post('/bulk-password-reset', [App\Http\Controllers\AdminController::class, 'bulkPasswordReset']);
    Route::post('/bulk-change-role', [App\Http\Controllers\AdminController::class, 'bulkChangeRole']);
    Route::post('/bulk-delete-users', [App\Http\Controllers\AdminController::class, 'bulkDeleteUsers']);
    
    // Password reset (admin only)
    Route::post('/users/{user}/reset-password', [PasswordResetController::class, 'sendResetEmail']);
    
    // System settings
    Route::get('/activity-logs', [App\Http\Controllers\AdminController::class, 'getActivityLogs']);
    Route::get('/attendance-logs', [App\Http\Controllers\AdminController::class, 'getAttendanceLogs']);
    Route::post('/update-admin-account', [App\Http\Controllers\AdminController::class, 'updateAdminAccount']);
    Route::get('/settings', [App\Http\Controllers\AdminController::class, 'getSettings']);
    Route::post('/settings', [App\Http\Controllers\AdminController::class, 'updateSetting']);
    Route::post('/update-manual-entry-code', [App\Http\Controllers\AdminController::class, 'updateManualEntryCode']);
    
    // Attendance monitoring
    Route::get('/attendance-stats', [App\Http\Controllers\AdminController::class, 'getAttendanceStats']);
    
    // Reports
    Route::get('/reports/attendance', [App\Http\Controllers\AdminReportController::class, 'getAttendanceReports']);
    Route::get('/reports/individual/{user}', [App\Http\Controllers\AdminReportController::class, 'getIndividualReport']);
    Route::get('/reports/export', [App\Http\Controllers\AdminReportController::class, 'exportReport']);
    Route::get('/reports/summary-stats', [App\Http\Controllers\AdminReportController::class, 'getSummaryStats']);
    
    // Absence request management (admin only)
    Route::patch('/absence-requests/{id}/approve', [AbsenceRequestController::class, 'approve']);
    Route::patch('/absence-requests/{id}/reject', [AbsenceRequestController::class, 'reject']);
});
