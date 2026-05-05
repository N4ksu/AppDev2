<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBehaviorRisk
{
    public function handle(Request $request, Closure $next)
    {
        if (session('behavior_risk', false)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Session terminated due to suspicious activity detected by AI.');
        }
        return $next($request);
    }
}
