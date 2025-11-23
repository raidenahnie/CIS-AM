<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\User;

echo "=== Checking November 12, 2025 Records ===\n\n";

$nov12Records = Attendance::where('date', '2025-11-12')
    ->with(['user', 'workplace'])
    ->get();

if ($nov12Records->isEmpty()) {
    echo "❌ NO attendance records found for Nov 12, 2025\n";
    echo "This is why it doesn't appear in Attendance History!\n\n";
} else {
    echo "✓ Found {$nov12Records->count()} attendance record(s) for Nov 12, 2025:\n\n";
    
    foreach ($nov12Records as $record) {
        echo "User: " . ($record->user ? $record->user->name : 'Unknown') . "\n";
        echo "Date: {$record->date->format('Y-m-d')}\n";
        echo "Status: {$record->status}\n";
        echo "Check-in: " . ($record->check_in_time ? $record->check_in_time->format('g:i A') : 'null') . "\n";
        echo "Check-out: " . ($record->check_out_time ? $record->check_out_time->format('g:i A') : 'null') . "\n";
        echo "Workplace: " . ($record->workplace ? $record->workplace->name : 'Unknown') . "\n";
        echo "Notes: " . ($record->notes ?? 'N/A') . "\n";
        echo str_repeat("-", 50) . "\n";
    }
}

echo "\n=== Why Nov 12 appears in Absence Records ===\n\n";

echo "The getAbsenceRecords() method:\n";
echo "1. Checks all workdays from start to end date (including today)\n";
echo "2. Gets attendance records WHERE check_in_time IS NOT NULL\n";
echo "3. Any workday WITHOUT a check-in is marked as absent\n\n";

echo "So if Nov 12 has:\n";
echo "- An attendance record with status='excused' BUT check_in_time=null\n";
echo "- It will be EXCLUDED from the 'attended days' list\n";
echo "- Therefore it appears as an absence in Absence Records\n\n";

// Check the actual query used in getAbsenceRecords
$userId = 1; // System Administrator
$startDate = \Carbon\Carbon::parse('2025-11-01', 'Asia/Manila');
$endDate = \Carbon\Carbon::now('Asia/Manila');

echo "=== What getAbsenceRecords sees ===\n\n";

$attendedDays = Attendance::where('user_id', $userId)
    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    ->whereNotNull('check_in_time')  // THIS IS THE KEY!
    ->pluck('date')
    ->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    })
    ->toArray();

echo "Attended days (with check_in_time NOT NULL):\n";
print_r($attendedDays);

echo "\n=== What getAttendanceHistory sees ===\n\n";

$allRecords = Attendance::where('user_id', $userId)
    ->orderBy('date', 'desc')
    ->limit(10)
    ->get();

echo "All attendance records (last 10):\n";
foreach ($allRecords as $record) {
    echo "- {$record->date->format('M j, Y')}: status={$record->status}, ";
    echo "check_in=" . ($record->check_in_time ? 'YES' : 'NULL') . "\n";
}

echo "\n=== THE PROBLEM ===\n\n";
echo "Nov 12 with status='excused' and check_in_time=NULL:\n";
echo "- DOES appear in getAttendanceHistory (shows ALL attendance records)\n";
echo "- Does NOT appear in attended days list for getAbsenceRecords\n";
echo "- Therefore shows as an absence in Absence Records\n\n";

echo "This is actually CORRECT behavior because:\n";
echo "- An excused absence IS still an absence\n";
echo "- It should appear in both places:\n";
echo "  1. Attendance History: as 'Excused' status\n";
echo "  2. Absence Records: as 'Excused' absence\n";
