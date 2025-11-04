<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;

$userId = 2;
$rows = Attendance::where('user_id', $userId)->orderBy('date', 'desc')->limit(20)->get();
$out = [];
foreach ($rows as $a) {
    $out[] = [
        'id' => $a->id,
        'date' => $a->date ? $a->date->format('Y-m-d') : null,
        'check_in_time' => $a->check_in_time ? $a->check_in_time->format('Y-m-d H:i:s') : null,
        'check_out_time' => $a->check_out_time ? $a->check_out_time->format('Y-m-d H:i:s') : null,
        'total_hours' => $a->total_hours,
        'status' => $a->status
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT);
