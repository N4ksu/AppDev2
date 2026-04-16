<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountNotLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if the account has been locked externally since they logged in
            if ($user->is_locked) {
                // If it's a manual lock (no duration) or a timed lock that hasn't expired yet
                if ($user->locked_until === null || now()->lessThan($user->locked_until)) {
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'This account has been locked due to security monitoring. You have been logged out.',
                    ]);
                } else {
                    // Lock has expired, we can clear it gracefully in the background
                    $user->update([
                        'is_locked' => false,
                        'failed_attempts' => 0,
                        'locked_until' => null,
                    ]);
                }
            }
        }

        return $next($request);
    }
}
