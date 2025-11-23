<?php
/**
 * Test Auto Check-Out System
 * Run this to test the auto check-out functionality locally
 * 
 * Usage: php public/cron/test-auto-checkout.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "===========================================\n";
echo "  AUTO CHECK-OUT SYSTEM TEST\n";
echo "===========================================\n\n";

// Test 1: Check system settings
echo "1️⃣  Testing System Settings...\n";
$notificationType = DB::table('system_settings')->where('key', 'notification_type')->value('value');
$smsApiUrl = DB::table('system_settings')->where('key', 'sms_api_url')->value('value');

echo "   ✓ Notification Type: " . ($notificationType ?? 'NOT SET') . "\n";
echo "   ✓ SMS API URL: " . ($smsApiUrl ?? 'NOT SET') . "\n\n";

// Test 2: Check for users currently checked in
echo "2️⃣  Checking Current Attendance...\n";
$checkedInUsers = DB::table('attendances')
    ->join('users', 'attendances.user_id', '=', 'users.id')
    ->whereNull('attendances.check_out_time')
    ->whereDate('attendances.check_in_time', date('Y-m-d'))
    ->select('users.name', 'users.email', 'attendances.check_in_time')
    ->get();

if ($checkedInUsers->count() > 0) {
    echo "   Found " . $checkedInUsers->count() . " user(s) currently checked in:\n";
    foreach ($checkedInUsers as $user) {
        $checkInTime = date('g:i A', strtotime($user->check_in_time));
        echo "   - {$user->name} ({$user->email}) - Checked in at {$checkInTime}\n";
    }
} else {
    echo "   ℹ️  No users currently checked in today\n";
}
echo "\n";

// Test 3: Simulate what would happen
echo "3️⃣  Simulation Mode...\n";
echo "   If auto check-out runs now:\n";

if ($checkedInUsers->count() > 0) {
    echo "   → Would check out " . $checkedInUsers->count() . " user(s) at " . date('g:i A') . "\n";
    echo "   → Would send " . ($notificationType ?? 'email') . " notifications\n";
} else {
    echo "   → Nothing to do (no users checked in)\n";
}
echo "\n";

// Test 4: Check email configuration
echo "4️⃣  Testing Email Configuration...\n";
$mailHost = env('MAIL_HOST');
$mailFrom = env('MAIL_FROM_ADDRESS');

echo "   ✓ Mail Host: " . ($mailHost ?? 'NOT SET') . "\n";
echo "   ✓ From Address: " . ($mailFrom ?? 'NOT SET') . "\n\n";

// Test 5: Check SMS configuration
echo "5️⃣  Testing SMS Configuration...\n";
$smsEnabled = config('services.sms.enabled');
$smsUrl = config('services.sms.api_url');

echo "   ✓ SMS Enabled: " . ($smsEnabled ? 'Yes' : 'No') . "\n";
echo "   ✓ SMS API URL: " . ($smsUrl ?? 'NOT SET') . "\n\n";

// Test 6: Cron schedule info
echo "6️⃣  Cron Schedule Information...\n";
echo "   Reminders: Run at 4:30 PM (16:30)\n";
echo "   Auto Check-Out: Run at 6:00 PM (18:00)\n";
echo "   Current Time: " . date('g:i A (H:i)') . "\n\n";

$currentTime = date('H:i');
if ($currentTime >= '16:30' && $currentTime < '16:45') {
    echo "   ⏰ REMINDER TIME - Would send reminders now!\n";
} elseif ($currentTime >= '18:00' && $currentTime < '18:15') {
    echo "   ⏰ AUTO CHECK-OUT TIME - Would auto check-out now!\n";
} else {
    echo "   ⏰ Outside schedule window (waiting...)\n";
}
echo "\n";

echo "===========================================\n";
echo "  TEST COMPLETE\n";
echo "===========================================\n\n";

echo "📝 Notes:\n";
echo "   - This is a DRY RUN (no actual changes made)\n";
echo "   - To run the actual cron job: php public/cron/daily-tasks.php\n";
echo "   - Make sure cron is set up in Hostinger to run every 15 minutes\n";
echo "\n";
