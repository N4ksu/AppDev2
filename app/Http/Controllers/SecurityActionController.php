<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityActionController extends Controller
{
    /**
     * Lock the authenticated user's account immediately.
     */
    public function lockAccount(Request $request)
    {
        $user = auth()->user();
        
        // Update user state
        $user->update([
            'is_locked' => true,
        ]);
        
        // Create forensic trace
        LoginLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'account_self_lock',
            'action_taken' => 'locked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'locked',
            'risk_score' => 0,
            'login_method' => 'system',
        ]);

        // Terminate sessions
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Account locked for your protection.');
    }

    /**
     * Report unrecognized activity and trigger emergency lockdown.
     */
    public function reportActivity(Request $request)
    {
        $user = auth()->user();
        
        // Create high-risk report trace before locking
        LoginLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'unrecognized_activity_report',
            'action_taken' => 'locked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'failed',
            'risk_score' => 100,
            'login_method' => 'forensic',
        ]);

        return $this->lockAccount($request);
    }
}
