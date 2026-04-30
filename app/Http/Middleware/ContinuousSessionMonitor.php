<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContinuousSessionMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For authenticated users, check their session risk score
        if (auth()->check()) {
            $riskScore = session('session_risk_score', 0);
            
            // If the risk score exceeds 85 (example threshold), trigger re-authentication or logout
            if ($riskScore > 85) {
                // Clear session and log them out
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with a warning message
                return redirect()->route('login')->withErrors([
                    'email' => 'Unusual activity detected. For your security, please log in again.',
                ]);
            }
        }

        return $next($request);
    }
}
