<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\AdminActivityLog;

class AuthController extends Controller
{
    // Show pages
    public function showLogin() {
        return view('auth.login');
    }

    // Registration disabled - users created by admin only
    /*
    public function showRegister() {
        return view('auth.register');
    }

    // Handle register
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Explicitly set as regular user
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
    */

    // Handle login
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            
            // Check if user is already logged in on another device/session
            if ($user->current_session_id) {
                // Check if that session still exists and is still active in the database
                $sessionLifetime = config('session.lifetime') * 60; // Convert minutes to seconds
                $expirationTime = now()->subSeconds($sessionLifetime)->timestamp;
                
                $existingSession = DB::table('sessions')
                    ->where('id', $user->current_session_id)
                    ->where('last_activity', '>', $expirationTime)
                    ->exists();
                
                if ($existingSession) {
                    // User is already logged in elsewhere with an active session, block this login
                    Auth::logout();
                    
                    Log::warning("Login blocked for {$user->email} - already logged in on another device", [
                        'existing_session_id' => $user->current_session_id
                    ]);
                    
                    // Return JSON for AJAX requests
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This account is already logged in on another device. Please log out from that device first.',
                            'errors' => [
                                'email' => ['This account is already logged in on another device. Please log out from that device first.']
                            ]
                        ], 401);
                    }
                    
                    return back()->withErrors([
                        'email' => 'This account is already logged in on another device. Please log out from that device first.',
                    ]);
                } else {
                    // Session doesn't exist or has expired, clear it
                    Log::info("Clearing expired/invalid session for {$user->email}", [
                        'old_session_id' => $user->current_session_id
                    ]);
                    $user->current_session_id = null;
                    $user->save();
                }
            }
            
            // Regenerate session for security
            $request->session()->regenerate();
            
            $newSessionId = session()->getId();
            
            // Update user's current session ID with the NEW session ID
            $user->current_session_id = $newSessionId;
            $user->save();
            
            Log::info("User {$user->email} logged in with session {$newSessionId}");
            
            // Log admin login
            if ($user->role === 'admin') {
                AdminActivityLog::log(
                    'login',
                    "Admin logged in: {$user->name} ({$user->email})",
                    'User',
                    $user->id,
                    null,
                    $user->id
                );
            }
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('dashboard'),
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ]
                ]);
            }
            
            return redirect()->route('dashboard');
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials provided.',
                'errors' => [
                    'email' => ['Invalid credentials provided.']
                ]
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Invalid credentials provided.',
        ]);
    }

    // Handle logout
    public function logout(Request $request) {
        $user = Auth::user();
        
        // Log admin logout before logging out
        if ($user && $user->role === 'admin') {
            AdminActivityLog::log(
                'logout',
                "Admin logged out: {$user->name} ({$user->email})",
                'User',
                $user->id,
                null,
                $user->id
            );
        }
        
        // Clear current session ID
        if ($user) {
            $user->current_session_id = null;
            $user->save();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }
}
