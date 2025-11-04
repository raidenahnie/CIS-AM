<?php

/**
 * Test script to verify special check-in fixes
 * Run with: php tests/test_special_checkin.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;

echo "=== Special Check-in Test ===" . PHP_EOL . PHP_EOL;

// Get a test user (first user in database)
$user = User::first();
if (!$user) {
    echo "ERROR: No users found in database. Please seed the database first." . PHP_EOL;
    exit(1);
}

echo "Testing with User ID: {$user->id} ({$user->name})" . PHP_EOL;

$today = now()->format('Y-m-d');
echo "Testing for date: {$today}" . PHP_EOL . PHP_EOL;

// Count attendance records for today
$attendanceRecords = Attendance::where('user_id', $user->id)
    ->whereDate('date', $today)
    ->get();

echo "--- Attendance Records for Today ---" . PHP_EOL;
echo "Count: " . $attendanceRecords->count() . PHP_EOL;

foreach ($attendanceRecords as $record) {
    echo "  ID: {$record->id}, Status: {$record->status}, Total Hours: {$record->total_hours} minutes" . PHP_EOL;
    echo "  Check-in: " . ($record->check_in_time ? $record->check_in_time->format('g:i A') : 'N/A') . PHP_EOL;
    echo "  Check-out: " . ($record->check_out_time ? $record->check_out_time->format('g:i A') : 'N/A') . PHP_EOL;
}

// Count special logs for today
$specialLogs = AttendanceLog::where('user_id', $user->id)
    ->whereDate('timestamp', $today)
    ->where(function($q) {
        $q->where('type', 'special')->orWhere('shift_type', 'special');
    })
    ->orderBy('timestamp', 'asc')
    ->get();

echo PHP_EOL . "--- Special Check-in Logs for Today ---" . PHP_EOL;
echo "Count: " . $specialLogs->count() . PHP_EOL;

foreach ($specialLogs as $log) {
    echo "  {$log->action} at " . $log->timestamp->format('g:i A') . " (Workplace ID: {$log->workplace_id})" . PHP_EOL;
}

// Calculate expected vs actual
$currentMonth = now()->startOfMonth();
$attendances = Attendance::where('user_id', $user->id)
    ->where('date', '>=', $currentMonth)
    ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6')
    ->get();

$presentDays = $attendances->where('status', '!=', 'absent')->pluck('date')->unique()->count();

echo PHP_EOL . "--- Monthly Statistics ---" . PHP_EOL;
echo "Total attendance records this month: " . $attendances->count() . PHP_EOL;
echo "Unique days present (should count each day once): {$presentDays}" . PHP_EOL;

// Show unique dates
$uniqueDates = $attendances->where('status', '!=', 'absent')->pluck('date')->unique();
echo "Unique dates: " . $uniqueDates->map(fn($d) => $d->format('M j'))->implode(', ') . PHP_EOL;

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
echo PHP_EOL . "Expected Behavior:" . PHP_EOL;
echo "1. Only ONE attendance record per day (even with multiple special check-ins)" . PHP_EOL;
echo "2. Days present count should match unique dates" . PHP_EOL;
echo "3. Total hours should sum ALL check-in/out pairs for the day" . PHP_EOL;
echo "4. Attendance percentage should never exceed 100%" . PHP_EOL;
