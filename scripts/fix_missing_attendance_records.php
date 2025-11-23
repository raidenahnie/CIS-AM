<?php
/**
 * Fix missing attendance records for approved absence request #1 (Nov 12-13)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

echo "=== Fixing Missing Attendance Records ===\n\n";

$absenceRequest = AbsenceRequest::find(1);

if (!$absenceRequest) {
    echo "❌ Absence request #1 not found\n";
    exit;
}

echo "Absence Request #1:\n";
echo "User: " . ($absenceRequest->user ? $absenceRequest->user->name : 'Unknown') . "\n";
echo "Date Range: {$absenceRequest->start_date->format('M j, Y')} - {$absenceRequest->end_date->format('M j, Y')}\n";
echo "Reason: {$absenceRequest->reason}\n";
echo "Status: {$absenceRequest->status}\n\n";

if ($absenceRequest->status !== 'approved') {
    echo "❌ Request is not approved. Cannot create attendance records.\n";
    exit;
}

// Get user's primary workplace
$user = User::find($absenceRequest->user_id);
$workplace = $user->workplaces()
    ->wherePivot('is_primary', true)
    ->first();

if (!$workplace) {
    $workplace = $user->workplaces()->first();
}

if (!$workplace) {
    echo "❌ User has no assigned workplace. Cannot create attendance records.\n";
    exit;
}

echo "Creating attendance records...\n";
echo "Workplace: {$workplace->name}\n\n";

$startDate = Carbon::parse($absenceRequest->start_date);
$endDate = Carbon::parse($absenceRequest->end_date);
$currentDate = $startDate->copy();
$created = 0;
$skipped = 0;

while ($currentDate->lte($endDate)) {
    if ($currentDate->isWeekday()) {
        $dateStr = $currentDate->format('Y-m-d');
        
        // Check if attendance record already exists
        $existingAttendance = Attendance::where('user_id', $absenceRequest->user_id)
            ->where('date', $dateStr)
            ->first();

        if ($existingAttendance) {
            echo "  ⏭️  {$dateStr} ({$currentDate->format('D')}): Already exists (status={$existingAttendance->status})\n";
            $skipped++;
        } else {
            // Create absence attendance record
            $attendance = Attendance::create([
                'user_id' => $absenceRequest->user_id,
                'workplace_id' => $workplace->id,
                'date' => $dateStr,
                'status' => 'excused',
                'notes' => 'Approved Leave: ' . $absenceRequest->reason . 
                           ($absenceRequest->admin_comment ? ' | Admin: ' . $absenceRequest->admin_comment : ''),
                'check_in_time' => null,
                'check_out_time' => null,
                'is_approved' => true,
                'approved_by' => $absenceRequest->admin_id,
                'approved_at' => $absenceRequest->reviewed_at,
            ]);
            
            echo "  ✓ {$dateStr} ({$currentDate->format('D')}): Created (ID={$attendance->id})\n";
            $created++;
        }
    } else {
        echo "  ⏭️  {$dateStr} ({$currentDate->format('D')}): Weekend - skipped\n";
    }
    $currentDate->addDay();
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Skipped (already exists): {$skipped}\n\n";

if ($created > 0) {
    echo "✅ SUCCESS! The missing attendance records have been created.\n";
    echo "Nov 12 and Nov 13 should now appear in Attendance History with 'Excused' status.\n\n";
    
    echo "Verify by:\n";
    echo "1. Refresh the Attendance History page\n";
    echo "2. Nov 12 should show:\n";
    echo "   - Check In: --\n";
    echo "   - Check Out: --\n";
    echo "   - Status: Excused (blue badge)\n";
    echo "   - Total Hours: 0 hrs\n";
} else {
    echo "ℹ️  No new records were created (all dates already had attendance records).\n";
}
