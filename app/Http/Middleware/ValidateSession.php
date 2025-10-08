<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation for guest routes (login, landing page, etc.)
        if (!Auth::check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        
        // Check if the current session ID matches the user's stored session ID
        if ($user->current_session_id && $user->current_session_id !== session()->getId()) {
            // Session mismatch - this should not happen under normal circumstances
            // but could occur if session was manually cleared from database
            Log::warning("Session mismatch for user {$user->email} - forcing logout", [
                'stored_session_id' => $user->current_session_id,
                'current_session_id' => session()->getId()
            ]);
            
            // Clear the stored session ID since it's invalid
            $user->current_session_id = null;
            $user->save();
            
            // Force logout
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerate();
            
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }
        
        return $next($request);
    }
}
