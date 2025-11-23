<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 1;
$controller = new App\Http\Controllers\Api\DashboardController();

// Create a mock request for current month
$request = new Illuminate\Http\Request([
    'start_date' => '2025-11-01',
    'end_date' => '2025-11-30'
]);

$response = $controller->getAbsenceRecords($userId, $request);
$data = json_decode($response->getContent(), true);

echo "\n📊 ABSENCE RECORDS TEST - Option 1 (Fixed Counter)\n";
echo "===================================================\n\n";

echo "Stats:\n";
echo "  Total absences: " . $data['stats']['total'] . "\n";
echo "  Unexcused: " . $data['stats']['unexcused'] . "\n";
echo "  Excused: " . $data['stats']['excused'] . " (approved absences)\n\n";

echo "Absence Records Table (only unexcused ones):\n";
if (empty($data['absences'])) {
    echo "  (No unexcused absences)\n";
} else {
    foreach ($data['absences'] as $absence) {
        echo "  📅 " . $absence['formatted_date'] . " (" . $absence['day_of_week'] . ")\n";
        echo "     Status: " . $absence['status_label'] . "\n";
        echo "     Reason: " . $absence['reason'] . "\n\n";
    }
}

echo "✅ EXPECTED BEHAVIOR:\n";
echo "   - Excused absences (Nov 11, 12, 13) should NOT appear in table\n";
echo "   - But should be counted in 'Excused' stat\n";
echo "   - Only unexcused absences appear in table\n\n";

// Verify excused count
if ($data['stats']['excused'] >= 2) {
    echo "✅ SUCCESS! Excused counter is working (counting approved absences)\n";
} else {
    echo "❌ ISSUE: Excused counter is " . $data['stats']['excused'] . " (expected at least 2 for Nov 12-13)\n";
}
