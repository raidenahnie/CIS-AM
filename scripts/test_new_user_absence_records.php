<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "\n🧪 TESTING NEW USER ABSENCE RECORDS\n";
echo "====================================\n\n";

// Find user ID 1
$user = \App\Models\User::find(1);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "User: {$user->name}\n";
echo "Account created: " . Carbon::parse($user->created_at)->format('M j, Y g:i A') . "\n";
echo "Account created day: " . Carbon::parse($user->created_at)->format('l') . "\n\n";

// Simulate the fix: count workdays only from account creation date
$startOfMonth = Carbon::now()->startOfMonth();
$endOfMonth = Carbon::now()->endOfMonth();
$userCreatedDate = Carbon::parse($user->created_at)->startOfDay();
$today = Carbon::now();

echo "Current Month: " . $startOfMonth->format('M Y') . "\n";
echo "Month range: {$startOfMonth->format('M j')} - {$endOfMonth->format('M j')}\n";
echo "User created: " . $userCreatedDate->format('M j, Y') . "\n\n";

// OLD LOGIC: Count all workdays in month
$oldWorkDays = 0;
for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
    if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 5 && $date->lte($today)) {
        $oldWorkDays++;
    }
}

// NEW LOGIC: Count workdays only from user creation date
$countStartDate = $startOfMonth->lt($userCreatedDate) ? $userCreatedDate->copy() : $startOfMonth->copy();
$newWorkDays = 0;
for ($date = $countStartDate->copy(); $date <= $endOfMonth; $date->addDay()) {
    if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 5 && $date->lte($today)) {
        $newWorkDays++;
    }
}

echo "📊 WORKDAY COUNT COMPARISON:\n";
echo "-----------------------------\n";
echo "OLD Logic (all days in month): {$oldWorkDays} workdays\n";
echo "NEW Logic (from creation date): {$newWorkDays} workdays\n";
echo "Difference: " . ($oldWorkDays - $newWorkDays) . " days\n\n";

// Test the actual API
$controller = new \App\Http\Controllers\Api\DashboardController();
$request = new Illuminate\Http\Request([
    'start_date' => $startOfMonth->format('Y-m-d'),
    'end_date' => $endOfMonth->format('Y-m-d')
]);

$response = $controller->getAbsenceRecords($user->id, $request);
$data = json_decode($response->getContent(), true);

echo "📋 ABSENCE RECORDS (from API):\n";
echo "-------------------------------\n";
echo "Total absences: " . $data['stats']['total'] . "\n";
echo "Unexcused: " . $data['stats']['unexcused'] . "\n";
echo "Excused: " . $data['stats']['excused'] . "\n\n";

if (!empty($data['absences'])) {
    echo "Unexcused Absence Dates:\n";
    foreach ($data['absences'] as $absence) {
        $absenceDate = Carbon::parse($absence['date']);
        $beforeCreation = $absenceDate->lt($userCreatedDate) ? " ⚠️ BEFORE ACCOUNT CREATION" : "";
        echo "  • " . $absence['formatted_date'] . " ({$absence['day_of_week']}){$beforeCreation}\n";
    }
} else {
    echo "✅ No unexcused absences found\n";
}

echo "\n✅ SUCCESS CRITERIA:\n";
echo "   - No absences should appear before " . $userCreatedDate->format('M j, Y') . "\n";
echo "   - Workday count should start from account creation date\n";

// Check if any absence is before creation date
$hasInvalidAbsence = false;
foreach ($data['absences'] as $absence) {
    if (Carbon::parse($absence['date'])->lt($userCreatedDate)) {
        $hasInvalidAbsence = true;
        break;
    }
}

if ($hasInvalidAbsence) {
    echo "\n❌ FAILED: Found absences before account creation date!\n";
} else {
    echo "\n✅ PASSED: No absences before account creation date\n";
}
