<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Update last_activity every 30 seconds to reduce database load
            $user = Auth::user();
            if (!$user->last_activity || $user->last_activity->lt(now()->subSeconds(30))) {
                $user->updateLastActivity();
            }
        }

        return $next($request);
    }
}
