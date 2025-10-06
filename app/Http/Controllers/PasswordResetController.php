<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
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
                'expires_at' => Carbon::now()->addHours(24), // Token expires in 24 hours
            ]);
            
            // Send email with reset link
            $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
            
            try {
                Mail::raw(
                    "Hello {$user->name},\n\n" .
                    "You are receiving this email because your administrator has initiated a password reset for your account.\n\n" .
                    "Please click the following link to reset your password:\n" .
                    "$resetUrl\n\n" .
                    "This password reset link will expire in 24 hours.\n\n" .
                    "If you did not request this password reset, please contact your administrator immediately.\n\n" .
                    "Best regards,\n" .
                    "Curriculum Implementation System",
                    function ($message) use ($user) {
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
