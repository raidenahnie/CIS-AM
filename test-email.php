<?php
/**
 * Simple Email Test Script
 * Tests if Laravel email configuration is working
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Email Configuration Test ===\n\n";

// Display current email configuration
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Get admin user email
$adminUser = \App\Models\User::where('role', 'admin')->first();

if (!$adminUser) {
    echo "ERROR: No admin user found in database\n";
    exit(1);
}

echo "Sending test email to: {$adminUser->email}\n\n";

try {
    \Illuminate\Support\Facades\Mail::raw(
        'This is a test email from CIS-AM system. If you received this, your email configuration is working correctly!',
        function ($message) use ($adminUser) {
            $message->to($adminUser->email)
                    ->subject('Test Email - CIS-AM System');
        }
    );
    
    echo "✓ Email sent successfully!\n";
    echo "Check your inbox at: {$adminUser->email}\n";
    echo "Note: If using Mailtrap, check your Mailtrap inbox at https://mailtrap.io\n";
    
} catch (\Exception $e) {
    echo "✗ Email sending failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Possible issues:\n";
    echo "1. Check your .env file has correct MAIL_* settings\n";
    echo "2. Verify MAIL_PASSWORD is correct (Mailtrap API token)\n";
    echo "3. Make sure your internet connection is working\n";
    echo "4. Check if port 587 is not blocked by firewall\n";
    exit(1);
}
