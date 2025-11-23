<?php
/**
 * Direct Test Notification Script
 * Simulates the admin test notification button
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Direct Notification Test ===\n\n";

// Get admin user
$admin = \App\Models\User::where('role', 'admin')->first();

if (!$admin) {
    echo "ERROR: No admin user found\n";
    exit(1);
}

echo "Testing as: {$admin->name} ({$admin->email})\n";

// Get current notification settings
$notificationType = \App\Models\SystemSetting::get('notification_type', 'email');
echo "Notification Type: {$notificationType}\n\n";

// Simulate the testNotification method
echo "Sending test notification...\n";

try {
    $message = "This is a test notification from CIS-AM Auto Check-Out System. If you received this, your notification settings are working correctly!";
    $subject = "Test Notification - CIS-AM";

    if ($notificationType === 'email' || $notificationType === 'both') {
        echo "Sending email to: {$admin->email}\n";
        
        \Illuminate\Support\Facades\Mail::raw($message, function($mail) use ($admin, $subject) {
            $mail->to($admin->email)
                 ->subject($subject);
        });
        
        echo "✓ Email sent successfully!\n";
    }

    if ($notificationType === 'sms' || $notificationType === 'both') {
        echo "SMS sending would happen here (requires phone number)\n";
        if ($admin->phone_number) {
            echo "Would send to: {$admin->phone_number}\n";
        } else {
            echo "⚠ Admin has no phone number set\n";
        }
    }

    echo "\n✓ Test completed successfully!\n";
    echo "Check your email inbox (or Mailtrap) for the test message.\n";

} catch (\Exception $e) {
    echo "\n✗ Error occurred!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
