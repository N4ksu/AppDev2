<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;
use App\Models\User;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;
        $riskService = app(\App\Services\RiskScoringService::class);
        $method = session('login_method', 'password');
        
        $risk = $riskService->calculateRisk($user, request(), $method, true, $user->email);

        LoginLog::create([
            'user_id' => $user->getAuthIdentifier(),
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success',
            'action' => 'login',
            'login_method' => $method,
            'failed_attempts' => $user->failed_attempts,
            'risk_score' => $risk['score'],
            'risk_level' => $risk['level'],
            'action_taken' => 'allowed',
        ]);

        if ($user) {
            $user->last_login_ip = request()->ip();

            if ($user->failed_attempts > 0 || $user->is_locked) {
                $user->failed_attempts = 0;
                $user->is_locked = false;
                $user->locked_until = null;
            }

            $user->save();
        }
    }
}
