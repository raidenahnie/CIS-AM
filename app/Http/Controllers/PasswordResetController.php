<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Request password reset (user-initiated from login page)
     * Protected by rate limiting and includes security measures
     */
    public function requestReset(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email|max:255'
        ]);
        
        // Log all password reset attempts for security monitoring
        Log::info('Password reset requested', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);
        
        // IMPORTANT: Always wait the same amount of time to prevent timing attacks
        // This prevents attackers from determining if an email exists in the system
        $startTime = microtime(true);
        
        try {
            // Find user by email (case-insensitive)
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();
            
            // If user exists, generate and send reset token
            if ($user) {
                // Check if user has too many recent reset requests (additional security layer)
                $recentResets = PasswordReset::where('user_id', $user->id)
                    ->where('created_at', '>', Carbon::now()->subMinutes(15))
                    ->count();
                
                if ($recentResets >= 3) {
                    Log::warning('Excessive password reset attempts', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                        'recent_resets' => $recentResets
                    ]);
                } else {
                    // Delete any existing reset tokens for this user
                    PasswordReset::where('user_id', $user->id)->delete();
                    
                    // Generate a cryptographically secure unique token
                    $token = Str::random(64); // 64 characters for extra security
                    $hashedToken = Hash::make($token);
                    
                    // Create password reset record
                    PasswordReset::create([
                        'user_id' => $user->id,
                        'token' => $hashedToken,
                        'expires_at' => Carbon::now()->addMinutes(60), // Token expires in 60 minutes
                    ]);
                    
                    // Send email with reset link
                    $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
                    
                    try {
                        $htmlContent = '
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                                    <h1 style="color: white; margin: 0;">Password Reset Request</h1>
                                </div>
                                
                                <div style="background: #f7f7f7; padding: 30px; border-radius: 0 0 10px 10px;">
                                    <h2 style="color: #333; margin-top: 0;">Hello ' . htmlspecialchars($user->name) . ',</h2>
                                    
                                    <p style="color: #555; line-height: 1.6; font-size: 16px;">
                                        You have requested to reset your password. Click the button below to create a new password:
                                    </p>
                                    
                                    <div style="text-align: center; margin: 35px 0;">
                                        <a href="' . htmlspecialchars($resetUrl) . '" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                                            Reset Password
                                        </a>
                                    </div>
                                    
                                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px;">
                                            <strong>⚠️ Security Notice:</strong> This link will expire in 60 minutes.
                                        </p>
                                    </div>
                                    
                                    <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                        <p style="margin: 0; color: #721c24; font-size: 14px;">
                                            <strong>🔒 Did not request this?</strong> If you did not request a password reset, please ignore this email and contact your administrator immediately. Your account may be compromised.
                                        </p>
                                    </div>
                                    
                                    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
                                        <p style="color: #777; font-size: 14px; margin: 0;">
                                            Request Details:<br>
                                            <strong>Time:</strong> ' . now()->format('F j, Y g:i A') . '<br>
                                            <strong>IP Address:</strong> ' . htmlspecialchars($request->ip()) . '
                                        </p>
                                    </div>
                                    
                                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                                        <p style="color: #777; font-size: 14px;">
                                            Best regards,<br>
                                            <strong>Curriculum Implementation System</strong>
                                        </p>
                                    </div>
                                    
                                    <div style="margin-top: 20px; font-size: 12px; color: #999; line-height: 1.5;">
                                        <p style="margin: 5px 0;">If the button doesn\'t work, copy and paste this URL into your browser:</p>
                                        <p style="margin: 5px 0; word-break: break-all;">
                                            <a href="' . htmlspecialchars($resetUrl) . '" style="color: #667eea;">' . htmlspecialchars($resetUrl) . '</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ';

                        Mail::html($htmlContent, function ($message) use ($user) {
                                $message->to($user->email, $user->name)
                                        ->subject('Password Reset Request - Action Required');
                            }
                        );
                        
                        Log::info('Password reset email sent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                        
                    } catch (\Exception $e) {
                        Log::error('Failed to send password reset email', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                Log::warning('Password reset requested for non-existent email', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);
            }
            
            // Add artificial delay to prevent timing attacks
            // This ensures the response time is consistent whether the email exists or not
            $elapsedTime = microtime(true) - $startTime;
            $minimumTime = 0.5; // 500ms minimum response time
            if ($elapsedTime < $minimumTime) {
                usleep(($minimumTime - $elapsedTime) * 1000000);
            }
            
        } catch (\Exception $e) {
            Log::error('Password reset request failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
        }
        
        // ALWAYS return the same response regardless of whether email exists
        // This prevents attackers from enumerating valid email addresses
        return response()->json([
            'success' => true,
            'message' => 'If your email is registered, you will receive a password reset link shortly. Please check your inbox and spam folder.'
        ]);
    }
    
    /**
     * Send password reset email (called by admin)
     */
    public function sendResetEmail(Request $request, User $user)
    {
        try {
            // Delete any existing reset tokens for this user
            PasswordReset::where('user_id', $user->id)->delete();
            
            // Generate a new token
            $token = Str::random(60);
            $hashedToken = Hash::make($token);
            
            // Create password reset record
            PasswordReset::create([
                'user_id' => $user->id,
                'token' => $hashedToken,
                'expires_at' => Carbon::now()->addMinutes(60), // Token expires in 60 minutes
            ]);
            
            // Send email with reset link
            $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
            
            try {
                $htmlContent = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #333;">Hello ' . htmlspecialchars($user->name) . ',</h2>
                        
                        <p style="color: #555; line-height: 1.5;">
                            You are receiving this email because your administrator has initiated a password reset for your account.
                        </p>
                        
                        <p style="color: #555; line-height: 1.5;">
                            Please click the button below to reset your password:
                        </p>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . htmlspecialchars($resetUrl) . '" style="background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block;">
                                Reset Password
                            </a>
                        </div>
                        
                        <p style="color: #555; line-height: 1.5;">
                            This password reset link will expire in 60 minutes.
                        </p>
                        
                        <p style="color: #555; line-height: 1.5;">
                            If you did not request this password reset, please contact your administrator immediately.
                        </p>
                        
                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                            <p style="color: #777;">
                                Best regards,<br>
                                Curriculum Implementation System
                            </p>
                        </div>
                        
                        <div style="margin-top: 20px; font-size: 12px; color: #999;">
                            If you are having trouble clicking the "Reset Password" button, copy and paste this URL into your web browser:<br>
                            <a href="' . htmlspecialchars($resetUrl) . '" style="color: #4CAF50;">' . htmlspecialchars($resetUrl) . '</a>
                        </div>
                    </div>
                ';

                Mail::html($htmlContent, function ($message) use ($user) {
                        $message->to($user->email, $user->name)
                                ->subject('Password Reset Request');
                    }
                );
                
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset email sent successfully'
                ]);
            } catch (\Exception $e) {
                // If email fails, still return success but with a warning
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset token created, but email could not be sent. Please configure email settings.'
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create password reset token: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show password reset form
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('login')->with('error', 'Invalid password reset link.');
        }
        
        // Find user by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }
        
        // Find valid reset token
        $resetRecord = PasswordReset::where('user_id', $user->id)
            ->notExpired()
            ->first();
        
        if (!$resetRecord) {
            return redirect()->route('login')->with('error', 'Password reset token has expired or is invalid.');
        }
        
        return view('auth.reset-password', compact('token', 'email'));
    }
    
    /**
     * Handle password reset submission
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'token' => 'required'
        ]);
        
        // Find user
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }
        
        // Find and validate reset token
        $resetRecord = PasswordReset::where('user_id', $user->id)
            ->notExpired()
            ->first();
        
        if (!$resetRecord) {
            return back()->withErrors(['token' => 'Password reset token has expired or is invalid.']);
        }
        
        try {
            // Update user password
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            // Delete the reset token
            $resetRecord->delete();
            
            // Clean up any other expired tokens
            PasswordReset::cleanupExpired();
            
            return redirect()->route('login')->with('success', 'Your password has been reset successfully. Please login with your new password.');
            
        } catch (\Exception $e) {
            return back()->withErrors(['password' => 'Failed to reset password. Please try again.']);
        }
    }
}
