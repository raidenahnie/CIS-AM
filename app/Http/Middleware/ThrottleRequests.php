<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    /**
     * Handle an incoming request with rate limiting
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Get unique key for this user/IP
        $key = $this->resolveRequestSignature($request);
        
        // Check current attempts
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            $retryAfter = Cache::get($key . ':timer');
            $waitTime = $retryAfter ? ceil(($retryAfter - time()) / 60) : $decayMinutes;
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'route' => $request->path(),
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many requests. Please try again in {$waitTime} minute(s).",
                    'retry_after' => $waitTime,
                    'rate_limit' => [
                        'limit' => $maxAttempts,
                        'remaining' => 0,
                        'reset' => $retryAfter
                    ]
                ], 429);
            }
            
            abort(429, "Too many requests. Please try again in {$waitTime} minute(s).");
        }
        
        // Increment attempts
        $this->hit($key, $decayMinutes);
        
        // Add rate limit headers
        $response = $next($request);
        
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $this->availableAt($decayMinutes)
        );
    }
    
    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use user ID if authenticated, otherwise use IP + user agent
        if ($user = $request->user()) {
            return 'throttle:user:' . $user->id . ':' . $request->path();
        }
        
        return 'throttle:ip:' . sha1(
            $request->ip() . '|' . $request->path() . '|' . $request->userAgent()
        );
    }
    
    /**
     * Increment the counter for a given key
     */
    protected function hit(string $key, int $decayMinutes): int
    {
        $key = $this->cleanRateLimiterKey($key);
        $expiresAt = now()->addMinutes($decayMinutes)->timestamp;
        
        if (!Cache::has($key)) {
            Cache::put($key . ':timer', $expiresAt, $decayMinutes * 60);
        }
        
        $hits = Cache::increment($key);
        Cache::put($key, $hits, $decayMinutes * 60);
        
        return $hits;
    }
    
    /**
     * Calculate remaining attempts
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = Cache::get($this->cleanRateLimiterKey($key), 0);
        return max(0, $maxAttempts - $attempts);
    }
    
    /**
     * Get the time when the rate limiter will reset
     */
    protected function availableAt(int $decayMinutes): int
    {
        return now()->addMinutes($decayMinutes)->timestamp;
    }
    
    /**
     * Clean rate limiter key
     */
    protected function cleanRateLimiterKey(string $key): string
    {
        return str_replace(':', '_', $key);
    }
    
    /**
     * Add rate limit headers to response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $resetTime): Response
    {
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remainingAttempts);
        $response->headers->set('X-RateLimit-Reset', $resetTime);
        
        return $response;
    }
}
