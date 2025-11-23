<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$log = DB::table('attendance_logs')
    ->where('user_id', 2)
    ->whereDate('timestamp', '2025-11-23')
    ->orderBy('timestamp', 'desc')
    ->first();

echo "Latest attendance log for user 2:\n";
print_r($log);

$user = DB::table('users')->where('id', 2)->first();
echo "\nUser 2 details:\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Phone: " . ($user->phone_number ?? 'Not set') . "\n";
