<?php
/**
 * Debug Query - Check what's actually in the database
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "Database Query Debug\n";
echo "==========================================\n\n";

// Check all attendance_logs from today without any date filtering
echo "1. ALL attendance_logs entries (no date filter):\n";
$allLogs = DB::table('attendance_logs')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($allLogs as $log) {
    echo "   ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Timestamp: {$log->timestamp}, Shift: {$log->shift_type}\n";
}

echo "\n2. Check-ins with action='check-in':\n";
$checkIns = DB::table('attendance_logs')
    ->where('action', 'check-in')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($checkIns as $log) {
    echo "   ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Timestamp: {$log->timestamp}, Shift: {$log->shift_type}\n";
}

echo "\n3. Using DATE() function:\n";
$today = date('Y-m-d');
$withDate = DB::select("SELECT * FROM attendance_logs WHERE DATE(timestamp) = ? AND action = 'check-in' ORDER BY id DESC LIMIT 10", [$today]);

foreach ($withDate as $log) {
    echo "   ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Timestamp: {$log->timestamp}, Shift: {$log->shift_type}\n";
}

echo "\n4. Using BETWEEN:\n";
$startOfDay = date('Y-m-d') . ' 00:00:00';
$endOfDay = date('Y-m-d') . ' 23:59:59';
$withBetween = DB::table('attendance_logs')
    ->where('action', 'check-in')
    ->whereBetween('timestamp', [$startOfDay, $endOfDay])
    ->orderBy('id', 'desc')
    ->get();

foreach ($withBetween as $log) {
    echo "   ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Timestamp: {$log->timestamp}, Shift: {$log->shift_type}\n";
}

echo "\n==========================================\n";
echo "Debug completed!\n";
echo "==========================================\n";
