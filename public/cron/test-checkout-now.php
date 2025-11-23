<?php
/**
 * Test Auto Checkout - Run Immediately
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

echo "==========================================\n";
echo "CIS-AM Auto Checkout TEST\n";
echo "Running at: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

autoCheckoutUsers();

echo "\n==========================================\n";
echo "Test completed!\n";
echo "==========================================\n";

/**
 * Auto check-out users at 6 PM
 */
function autoCheckoutUsers() {
    try {
        $checkOutTime = date('Y-m-d H:i:s'); // Use current time for testing
        $today = date('Y-m-d');
        
        echo "   DEBUG: Looking for check-ins on date: {$today}\n";
        echo "   DEBUG: Checkout time will be: {$checkOutTime}\n\n";
        
        // Get all check-ins from today (AM, PM, and Special)
        $checkIns = DB::table('attendance_logs')
            ->join('users', 'attendance_logs.user_id', '=', 'users.id')
            ->whereDate('attendance_logs.timestamp', $today)
            ->where('attendance_logs.action', 'check_in')
            ->select('attendance_logs.*', 'users.name', 'users.email', 'users.phone_number')
            ->get();
        
        echo "   DEBUG: Found {$checkIns->count()} total check-ins today\n\n";
        
        if ($checkIns->count() > 0) {
            foreach ($checkIns as $ci) {
                echo "   DEBUG: Check-in found:\n";
                echo "      - User: {$ci->name} (ID: {$ci->user_id})\n";
                echo "      - Time: {$ci->timestamp}\n";
                echo "      - Attendance ID: {$ci->attendance_id}\n";
                echo "      - Shift Type: {$ci->shift_type}\n";
                echo "      - Action: {$ci->action}\n\n";
            }
        } else {
            echo "   ⚠️  NO CHECK-INS FOUND FOR TODAY!\n";
            echo "   This could mean:\n";
            echo "   1. Date format mismatch in database\n";
            echo "   2. No check-ins recorded today\n";
            echo "   3. Check-ins are in a different table\n\n";
            return;
        }
        
        // Filter check-ins that don't have a checkout with matching attendance_id
        $needingCheckout = $checkIns->filter(function($checkIn) use ($today) {
            // Check if there's a checkout with the same attendance_id
            $checkouts = DB::table('attendance_logs')
                ->where('user_id', $checkIn->user_id)
                ->whereDate('timestamp', $today)
                ->where('attendance_id', $checkIn->attendance_id)
                ->where('action', 'check_out')
                ->get();
            
            $hasCheckout = $checkouts->count() > 0;
            
            if (!$hasCheckout) {
                echo "   ✅ User {$checkIn->user_id} ({$checkIn->name}) NEEDS checkout - attendance_id: {$checkIn->attendance_id}, shift: {$checkIn->shift_type}\n";
            } else {
                echo "   ⏭️  User {$checkIn->user_id} ({$checkIn->name}) already has checkout - attendance_id: {$checkIn->attendance_id}\n";
            }
            
            return !$hasCheckout;
        });
        
        echo "\n   📊 SUMMARY: {$needingCheckout->count()} users need auto check-out\n\n";
        
        if ($needingCheckout->count() === 0) {
            echo "   ℹ️  All users are already checked out. Nothing to do.\n";
            return;
        }
        
        foreach ($needingCheckout as $checkIn) {
            $shiftLabel = strtoupper($checkIn->shift_type);
            
            echo "   🔄 Creating checkout for:\n";
            echo "      - User: {$checkIn->name} (ID: {$checkIn->user_id})\n";
            echo "      - Attendance ID: {$checkIn->attendance_id}\n";
            echo "      - Shift: {$shiftLabel}\n";
            
            // Create auto checkout log
            $inserted = DB::table('attendance_logs')->insert([
                'user_id' => $checkIn->user_id,
                'workplace_id' => $checkIn->workplace_id,
                'attendance_id' => $checkIn->attendance_id,
                'action' => 'check_out',
                'type' => $checkIn->type,
                'shift_type' => $checkIn->shift_type,
                'sequence' => ($checkIn->sequence ?? 0) + 1,
                'timestamp' => $checkOutTime,
                'latitude' => $checkIn->latitude,
                'longitude' => $checkIn->longitude,
                'notes' => 'Auto checked-out by system (TEST)',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($inserted) {
                echo "   ✅ Successfully created {$shiftLabel} checkout log!\n\n";
            } else {
                echo "   ❌ Failed to create {$shiftLabel} checkout log!\n\n";
            }
            
            $checkInTime = date('g:i A', strtotime($checkIn->timestamp));
            $message = "TEST: You have been automatically checked out. You checked in at {$checkInTime}. This is a test notification.";
            
            $user = (object)[
                'name' => $checkIn->name,
                'email' => $checkIn->email,
                'phone_number' => $checkIn->phone_number
            ];
            
            sendNotification($user, $message, 'auto-checkout');
            echo "   📧 Notification sent to: {$checkIn->name} ({$checkIn->email})\n\n";
        }
        
        Log::info("TEST: Auto checked-out {$needingCheckout->count()} users");
        
    } catch (Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n";
        echo $e->getTraceAsString() . "\n";
        Log::error("Auto check-out test failed: " . $e->getMessage());
    }
}

/**
 * Send notification based on admin settings
 */
function sendNotification($user, $message, $type = 'reminder') {
    try {
        // Get notification settings
        $notificationType = DB::table('system_settings')
            ->where('key', 'notification_type')
            ->value('value') ?? 'email';
        
        // Send email if enabled
        if ($notificationType === 'email' || $notificationType === 'both') {
            sendEmail($user->email, $user->name, $message, $type);
        }
        
        // Send SMS if enabled
        if (($notificationType === 'sms' || $notificationType === 'both') && !empty($user->phone_number)) {
            sendSMS($user->phone_number, $message);
        }
        
    } catch (Exception $e) {
        echo "   ⚠️  Notification failed for {$user->email}: " . $e->getMessage() . "\n";
        Log::warning("Notification failed for {$user->email}: " . $e->getMessage());
    }
}

/**
 * Send email notification
 */
function sendEmail($email, $name, $message, $type) {
    try {
        $subject = $type === 'reminder' ? 'Reminder: Check Out' : 'Auto Check-Out Notification (TEST)';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>CIS-AM Attendance System - TEST</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$name}</strong>,</p>
                    <p>{$message}</p>
                </div>
                <div class='footer'>
                    <p>This is a TEST notification from CIS-AM Attendance Monitoring System</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        Mail::html($html, function ($mail) use ($email, $subject) {
            $mail->to($email)
                 ->subject($subject);
        });
        
    } catch (Exception $e) {
        throw new Exception("Email send failed: " . $e->getMessage());
    }
}

/**
 * Send SMS notification
 */
function sendSMS($phone, $message) {
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
            'phone' => $phone,
            'message' => $message,
            'senderName' => 'CIS-AM System'
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
        
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("SMS API returned HTTP {$httpCode}: {$response}");
        }
        
    } catch (Exception $e) {
        throw new Exception("SMS send failed: " . $e->getMessage());
    }
}
