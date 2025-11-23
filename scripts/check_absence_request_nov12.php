<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AbsenceRequest;
use App\Models\Attendance;

echo "=== Checking Absence Requests for Nov 12, 2025 ===\n\n";

$nov12Requests = AbsenceRequest::where(function($query) {
    $query->where('start_date', '<=', '2025-11-12')
          ->where('end_date', '>=', '2025-11-12');
})
->with(['user', 'admin'])
->get();

if ($nov12Requests->isEmpty()) {
    echo "❌ NO absence requests found covering Nov 12, 2025\n\n";
} else {
    echo "✓ Found {$nov12Requests->count()} absence request(s) covering Nov 12, 2025:\n\n";
    
    foreach ($nov12Requests as $request) {
        echo "ID: {$request->id}\n";
        echo "User: " . ($request->user ? $request->user->name : 'Unknown') . "\n";
        echo "Start Date: {$request->start_date->format('M j, Y')}\n";
        echo "End Date: {$request->end_date->format('M j, Y')}\n";
        echo "Reason: {$request->reason}\n";
        echo "Status: {$request->status}\n";
        echo "Approved by: " . ($request->admin ? $request->admin->name : 'N/A') . "\n";
        echo "Reviewed at: " . ($request->reviewed_at ? $request->reviewed_at->format('M j, Y g:i A') : 'N/A') . "\n";
        echo str_repeat("-", 50) . "\n\n";
        
        // Check if attendance records were created for this request
        if ($request->status === 'approved') {
            echo "Checking if attendance records were created...\n";
            
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekday()) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $attendance = Attendance::where('user_id', $request->user_id)
                        ->where('date', $dateStr)
                        ->first();
                    
                    if ($attendance) {
                        echo "  ✓ {$dateStr}: Attendance record exists (status={$attendance->status})\n";
                    } else {
                        echo "  ❌ {$dateStr}: NO attendance record found!\n";
                    }
                }
                $currentDate->addDay();
            }
            echo "\n";
        }
    }
}

echo "\n=== THE ISSUE ===\n\n";
echo "If an absence request for Nov 12 was approved BUT:\n";
echo "1. The attendance record was NOT created (or failed to create)\n";
echo "2. Then Nov 12 will:\n";
echo "   - Show in Absence Records (as unexcused, because no check-in)\n";
echo "   - NOT show in Attendance History (no record exists)\n\n";

echo "=== SOLUTION ===\n\n";
echo "When approving an absence request, the system should create\n";
echo "attendance records with status='excused' for each workday.\n";
echo "If this failed for Nov 12, we need to manually create it or re-approve.\n";
