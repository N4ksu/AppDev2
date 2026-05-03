<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\LoginLog;
use App\Models\User;
use Laravel\Fortify\Fortify;

class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;
        $submittedEmail = $event->credentials['email'] ?? null;
        $method = request()->is('webauthn/login*') ? 'passkey' : 'password';
        $riskService = app(\App\Services\RiskScoringService::class);
        $risk = $riskService->calculateRisk($user, request(), $method, false, $submittedEmail);

        if ($user) {
            // Fetch dynamic security settings
            $settings = \App\Models\SecuritySetting::first() ?? new \App\Models\SecuritySetting([
                'max_failed_attempts' => 3,
                'lock_duration_minutes' => 15
            ]);

            $alreadyLocked = ($user->is_locked && ($user->locked_until === null || now()->lessThan($user->locked_until)));
            
            // Increment attempts only if NOT already locked
            if (!$alreadyLocked) {
                $user->failed_attempts += 1;
            }

            $isLockingNow = false;
            // PROACTIVE LOCK: If risk is HIGH (>=70) or max attempts reached
            if (!$alreadyLocked && ($user->failed_attempts >= $settings->max_failed_attempts || $risk['score'] >= 70)) {
                $user->is_locked    = true;
                $lockMinutes = ($risk['score'] >= 70) ? 60 : $settings->lock_duration_minutes; // High risk gets 1 hour lock
                $user->locked_until = now()->addMinutes($lockMinutes);
                $isLockingNow = true;
            }

            $user->save();

            // Determine status for this log entry
            $status = ($alreadyLocked || $isLockingNow) ? 'locked' : 'failed';

            LoginLog::create([
                'user_id'    => $user->id,
                'email'      => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status'     => $status,
                'action'     => 'login',
                'login_method' => $method,
                'failed_attempts' => $user->failed_attempts,
                'risk_score'   => $risk['score'],
                'risk_level'   => $risk['level'],
                'action_taken' => $status === 'locked' ? 'locked' : 'denied',
            ]);
        } else {
            // Log failed attempts for non-existent users
            LoginLog::create([
                'user_id'    => null,
                'email'      => $submittedEmail,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status'     => 'failed',
                'action'     => 'login',
                'login_method' => $method,
                'failed_attempts' => 0,
                'risk_score'   => $risk['score'],
                'risk_level'   => $risk['level'],
                'action_taken' => 'denied',
            ]);
        }
    }
}
