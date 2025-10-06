<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            $request->session()->regenerate();
            
            $user = Auth::user();
            
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
            
            return redirect()->route('dashboard');
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
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }
}
