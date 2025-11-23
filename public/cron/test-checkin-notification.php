<?php
/**
 * Test Check-In Notification
 * Run this to test if check-in email/SMS works
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

echo "Testing Check-In Notification...\n\n";

// Get your user
$user = DB::table('users')->where('id', 1)->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "📧 Sending to: {$user->email}\n";
echo "📱 Phone: " . ($user->phone_number ?? 'Not set') . "\n\n";

$message = "You have successfully checked in at 7:30 PM (TEST notification) at CIS Office. Have a productive day!";

// Get notification type
$notificationType = DB::table('system_settings')
    ->where('key', 'notification_type')
    ->value('value') ?? 'email';

echo "Notification Type: {$notificationType}\n\n";

// Send Email
if ($notificationType === 'email' || $notificationType === 'both') {
    try {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #10B981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .success-icon { font-size: 48px; text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✓ Check-In Successful</h2>
                </div>
                <div class='content'>
                    <div class='success-icon'>✓</div>
                    <p>Hello <strong>{$user->name}</strong>,</p>
                    <p>{$message}</p>
                    <p style='margin-top: 20px;'>
                        <a href='" . url('/dashboard') . "' class='button'>View Dashboard</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>This is an automated TEST message from CIS-AM Attendance Monitoring System</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        Mail::html($html, function ($mail) use ($user) {
            $mail->to($user->email)->subject('Check-In Successful (TEST)');
        });
        
        echo "✅ Email sent successfully!\n";
        
    } catch (Exception $e) {
        echo "❌ Email failed: " . $e->getMessage() . "\n";
    }
}

// Send SMS
if (($notificationType === 'sms' || $notificationType === 'both') && !empty($user->phone_number)) {
    try {
        $smsApiUrl = DB::table('system_settings')
            ->where('key', 'sms_api_url')
            ->value('value');
        
        if (!$smsApiUrl) {
            throw new Exception('SMS API URL not configured');
        }
        
        $smsApiKey = getenv('SMS_API_KEY');
        if (!$smsApiKey) {
            throw new Exception('SMS_API_KEY not set in environment');
        }
        
        $payload = json_encode([
            'gatewayUrl' => 'api.sms-gate.app',
            'phone' => $user->phone_number,
            'message' => $message,
            'senderName' => 'CIS-AM'
        ]);
        
        $ch = curl_init($smsApiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $smsApiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "✅ SMS sent successfully! (HTTP {$httpCode})\n";
        } else {
            echo "❌ SMS failed: HTTP {$httpCode} - {$response}\n";
        }
        
    } catch (Exception $e) {
        echo "❌ SMS failed: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Test completed\n";
