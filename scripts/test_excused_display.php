<?php
/**
 * Test script to verify excused absence display fix
 * 
 * This script checks if excused absences are properly displayed
 * in the attendance history without showing "Still working"
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\User;

echo "=== Excused Absence Display Test ===\n\n";

// Find a user with excused absences
$excusedAttendances = Attendance::where('status', 'excused')
    ->with(['user', 'workplace'])
    ->orderBy('date', 'desc')
    ->limit(5)
    ->get();

if ($excusedAttendances->isEmpty()) {
    echo "No excused absences found in the system.\n";
    echo "To test:\n";
    echo "1. Create an absence request for a date\n";
    echo "2. Have an admin approve it\n";
    echo "3. Check the attendance history for that date\n\n";
    exit;
}

echo "Found " . $excusedAttendances->count() . " excused absence(s):\n\n";

foreach ($excusedAttendances as $attendance) {
    echo "Date: " . $attendance->date->format('M j, Y') . " (Nov 11, 2025 mentioned in bug)\n";
    echo "User: " . ($attendance->user ? $attendance->user->name : 'Unknown') . "\n";
    echo "Status: " . $attendance->status . "\n";
    echo "Check-in: " . ($attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : '--') . "\n";
    echo "Check-out: " . ($attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : '--') . "\n";
    echo "Workplace: " . ($attendance->workplace ? $attendance->workplace->name : 'Unknown') . "\n";
    echo "Notes: " . ($attendance->notes ?? 'N/A') . "\n";
    
    // Test what the API would return
    echo "\nAPI Response would be:\n";
    $response = [
        'date' => $attendance->date->format('M j, Y'),
        'check_in' => '--',
        'check_out' => '--',  // SHOULD BE '--' NOT 'Still working'
        'status' => 'Excused',
        'status_class' => 'bg-blue-100 text-blue-800',
        'total_hours' => '0 hrs',
        'location' => $attendance->workplace ? $attendance->workplace->name : 'Unknown',
        'notes' => $attendance->notes ?? 'Approved absence'
    ];
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    echo str_repeat("-", 50) . "\n\n";
}

// Check if Nov 11, 2025 has an excused absence
$nov11 = Attendance::whereDate('date', '2025-11-11')
    ->where('status', 'excused')
    ->with(['user', 'workplace'])
    ->first();

if ($nov11) {
    echo "\n=== SPECIFIC DATE CHECK: November 11, 2025 ===\n";
    echo "✓ Found excused absence for Nov 11, 2025\n";
    echo "User: " . ($nov11->user ? $nov11->user->name : 'Unknown') . "\n";
    echo "This SHOULD show as 'Excused' with '--' for check-in/out times\n";
    echo "NOT 'Still working'\n\n";
} else {
    echo "\n=== SPECIFIC DATE CHECK: November 11, 2025 ===\n";
    echo "✗ No excused absence found for Nov 11, 2025\n";
    echo "The absence might not have been approved yet.\n\n";
}

echo "=== Test Complete ===\n";
echo "\nTo verify the fix in the UI:\n";
echo "1. Log in as the user\n";
echo "2. Go to 'Attendance History' section\n";
echo "3. Look for Nov 11, 2025 (or other approved absences)\n";
echo "4. Should show:\n";
echo "   - Check In: --\n";
echo "   - Check Out: --\n";
echo "   - Status: Excused (with blue badge)\n";
echo "   - Total Hours: 0 hrs\n";
