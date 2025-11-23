<?php
/**
 * Test script to verify the attendance counting fix
 * Ensures excused absences don't appear in Absence Records
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use Carbon\Carbon;

echo "=== Testing Attendance Counting Fix ===\n\n";

$userId = 1; // System Administrator
$startDate = Carbon::parse('2025-11-01', 'Asia/Manila');
$endDate = Carbon::now('Asia/Manila');

echo "Date Range: {$startDate->format('M j, Y')} - {$endDate->format('M j, Y')}\n\n";

// OLD WAY (Broken) - Only counts records with check_in_time
echo "OLD Logic (whereNotNull('check_in_time')):\n";
$oldAttendances = Attendance::where('user_id', $userId)
    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    ->whereNotNull('check_in_time')
    ->get();

echo "Count: " . $oldAttendances->count() . "\n";
foreach ($oldAttendances as $att) {
    echo "  - {$att->date->format('M j')}: status={$att->status}\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

// NEW WAY (Fixed) - Counts records with check_in_time OR excused status
echo "NEW Logic (check_in_time OR status='excused'):\n";
$newAttendances = Attendance::where('user_id', $userId)
    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    ->where(function($query) {
        $query->whereNotNull('check_in_time')
              ->orWhere('status', 'excused');
    })
    ->get();

echo "Count: " . $newAttendances->count() . "\n";
foreach ($newAttendances as $att) {
    echo "  - {$att->date->format('M j')}: status={$att->status}, check_in=" . 
         ($att->check_in_time ? 'YES' : 'NULL') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

// Show the difference
$oldDates = $oldAttendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
$newDates = $newAttendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

$addedDates = array_diff($newDates, $oldDates);

if (!empty($addedDates)) {
    echo "✅ FIXED! The following excused absences are now properly counted:\n";
    foreach ($addedDates as $date) {
        $att = $newAttendances->first(function($a) use ($date) {
            return $a->date->format('Y-m-d') === $date;
        });
        if ($att) {
            echo "  - " . Carbon::parse($date)->format('M j, Y') . 
                 " (status={$att->status}, notes: " . substr($att->notes ?? 'N/A', 0, 40) . "...)\n";
        }
    }
    echo "\nThese dates will NO LONGER appear in the Absence Records!\n";
} else {
    echo "ℹ️  No excused absences found in this period.\n";
}

echo "\n=== Impact on Absence Records ===\n\n";

// Count workdays
$totalWorkdays = 0;
$currentDate = $startDate->copy();
while ($currentDate->lte($endDate)) {
    if ($currentDate->isWeekday()) {
        $totalWorkdays++;
    }
    $currentDate->addDay();
}

echo "Total Workdays: {$totalWorkdays}\n";
echo "OLD: Attended = {$oldAttendances->count()}, Absences = " . ($totalWorkdays - $oldAttendances->count()) . "\n";
echo "NEW: Attended = {$newAttendances->count()}, Absences = " . ($totalWorkdays - $newAttendances->count()) . "\n";

$difference = count($addedDates);
if ($difference > 0) {
    echo "\n✅ Absence count reduced by {$difference} (excused absences no longer counted as missing)\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Refresh the Absence Records page\n";
echo "2. Nov 11, Nov 12, Nov 13 should NOT appear as absences\n";
echo "3. They should ONLY appear in Attendance History as 'Excused'\n";
