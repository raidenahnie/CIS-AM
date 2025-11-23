<?php
/**
 * Auto Checkout Cron Job for Hostinger
 * 
 * Setup in Hostinger cPanel → Cron Jobs:
 * Schedule: */15 * * * * (Every 15 minutes)
 * Command: /usr/bin/php /home/username/public_html/public/cron/auto-checkout.php
 * 
 * This will:
 * - Send reminders at 4:30 PM - 4:45 PM
 * - Auto checkout users at 6:00 PM - 6:15 PM
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

$now = date('H:i');
$today = date('Y-m-d');

echo "[" . date('Y-m-d H:i:s') . "] CIS-AM Auto Checkout Cron Job\n";

// 1. Send reminders at 4:30 PM (16:30 - 16:45)
if ($now >= '16:30' && $now < '16:45') {
    echo "📢 Sending check-out reminders...\n";
    sendCheckoutReminders();
}

// 2. Auto check-out at 6:00 PM (18:00 - 18:15)
if ($now >= '18:00' && $now < '18:15') {
    echo "🔒 Auto checking-out users...\n";
    autoCheckoutUsers();
}

echo "✓ Cron job completed\n\n";

/**
 * Send reminders to users still checked in
 */
function sendCheckoutReminders() {
    try {
        $checkIns = DB::table('attendance_logs')
            ->join('users', 'attendance_logs.user_id', '=', 'users.id')
            ->whereDate('attendance_logs.timestamp', date('Y-m-d'))
            ->where('attendance_logs.action', 'check_in')
            ->select('attendance_logs.*', 'users.name', 'users.email', 'users.phone_number')
            ->get();
        
        $needingReminder = $checkIns->filter(function($checkIn) {
            $hasCheckout = DB::table('attendance_logs')
                ->where('user_id', $checkIn->user_id)
                ->whereDate('timestamp', date('Y-m-d'))
                ->where('shift_type', $checkIn->shift_type)
                ->where('action', 'check_out')
                ->exists();
            return !$hasCheckout;
        });
        
        echo "   Found " . $needingReminder->count() . " users still checked in\n";
        
        foreach ($needingReminder as $checkIn) {
            $checkInTime = date('g:i A', strtotime($checkIn->timestamp));
            $shiftLabel = strtoupper($checkIn->shift_type);
            $message = "Reminder: You checked in at {$checkInTime} ({$shiftLabel} shift). Don't forget to check out before leaving work. Auto check-out will happen at 6 PM.";
            
            $user = (object)[
                'name' => $checkIn->name,
                'email' => $checkIn->email,
                'phone_number' => $checkIn->phone_number
            ];
            
            sendNotification($user, $message, 'reminder');
            echo "   ✓ Sent reminder to: {$checkIn->name}\n";
        }
        
        Log::info("Sent check-out reminders to " . $needingReminder->count() . " users");
        
    } catch (Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
        Log::error("Reminder task failed: " . $e->getMessage());
    }
}

/**
 * Auto check-out users at 6 PM
 */
function autoCheckoutUsers() {
    try {
        $checkOutTime = date('Y-m-d') . ' 18:00:00';
        $today = date('Y-m-d');
        
        $checkIns = DB::table('attendance_logs')
            ->join('users', 'attendance_logs.user_id', '=', 'users.id')
            ->whereDate('attendance_logs.timestamp', $today)
            ->where('attendance_logs.action', 'check_in')
            ->select('attendance_logs.*', 'users.name', 'users.email', 'users.phone_number')
            ->get();
        
        $needingCheckout = $checkIns->filter(function($checkIn) use ($today) {
            $hasCheckout = DB::table('attendance_logs')
                ->where('user_id', $checkIn->user_id)
                ->whereDate('timestamp', $today)
                ->where('attendance_id', $checkIn->attendance_id)
                ->where('action', 'check_out')
                ->exists();
            return !$hasCheckout;
        });
        
        echo "   Found {$needingCheckout->count()} users to auto check-out\n";
        
        foreach ($needingCheckout as $checkIn) {
            $shiftLabel = strtoupper($checkIn->shift_type);
            
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
                'notes' => 'Auto checked-out by system at 6 PM',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($inserted) {
                echo "   ✓ Auto checked-out: {$checkIn->name} - {$shiftLabel} shift\n";
            }
            
            $checkInTime = date('g:i A', strtotime($checkIn->timestamp));
            $message = "You have been automatically checked out at 6:00 PM ({$shiftLabel} shift). You checked in at {$checkInTime}. Have a great evening!";
            
            $user = (object)[
                'name' => $checkIn->name,
                'email' => $checkIn->email,
                'phone_number' => $checkIn->phone_number
            ];
            
            sendNotification($user, $message, 'auto-checkout');
        }
        
        Log::info("Auto checked-out {$needingCheckout->count()} users at 6 PM");
        
    } catch (Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
        Log::error("Auto check-out task failed: " . $e->getMessage());
    }
}

/**
 * Send notification based on admin settings
 */
function sendNotification($user, $message, $type = 'reminder') {
    try {
        $notificationType = DB::table('system_settings')
            ->where('key', 'notification_type')
            ->value('value') ?? 'email';
        
        if ($notificationType === 'email' || $notificationType === 'both') {
            sendEmail($user->email, $user->name, $message, $type);
        }
        
        if (($notificationType === 'sms' || $notificationType === 'both') && !empty($user->phone_number)) {
            sendSMS($user->phone_number, $message);
        }
        
    } catch (Exception $e) {
        Log::warning("Notification failed for {$user->email}: " . $e->getMessage());
    }
}

/**
 * Send email notification
 */
function sendEmail($email, $name, $message, $type) {
    try {
        $subject = $type === 'reminder' ? 'Reminder: Check Out' : 'Auto Check-Out Notification';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #10B981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>CIS-AM Attendance System</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$name}</strong>,</p>
                    <p>{$message}</p>
                    <p style='margin-top: 20px;'>
                        <a href='" . url('/dashboard') . "' class='button'>Go to Dashboard</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from CIS-AM Attendance Monitoring System</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        Mail::html($html, function ($mail) use ($email, $subject) {
            $mail->to($email)->subject($subject);
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
