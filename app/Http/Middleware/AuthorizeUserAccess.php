<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AuthorizeUserAccess
{
    /**
     * Handle an incoming request.
     * Ensures users can only access their own data unless they are an admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedUserId = Auth::id();
        
        // Check if user is authenticated
        if (!$authenticatedUserId) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to access this resource.'
            ], 401);
        }

        // Get the userId from route parameter
        $requestedUserId = $request->route('userId');
        
        // If there's no userId in route, allow (some endpoints don't use it)
        if (!$requestedUserId) {
            return $next($request);
        }

        // Admins can access any user's data
        $user = Auth::user();
        if ($user && $user->is_admin) {
            return $next($request);
        }

        // Regular users can only access their own data
        if ($authenticatedUserId != $requestedUserId) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this data.'
            ], 403);
        }

        return $next($request);
    }
}
