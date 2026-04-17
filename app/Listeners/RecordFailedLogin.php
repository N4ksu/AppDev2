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
        // null as $event->user. The submitted email is in $event->credentials instead.
        $submittedEmail = $event->credentials[Fortify::username()] ?? null;

        // Look up the real user by the submitted email so we can track failed_attempts.
        $user = $submittedEmail ? User::where('email', $submittedEmail)->first() : null;

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
            if (!$alreadyLocked && $user->failed_attempts >= $settings->max_failed_attempts) {
                $user->is_locked    = true;
                $user->locked_until = now()->addMinutes($settings->lock_duration_minutes);
                $isLockingNow = true;
            }

            $user->save();

            // Determine status for this log entry
            $status = ($alreadyLocked || $isLockingNow) ? 'locked' : 'failed';

            $log = LoginLog::create([
                'user_id'    => $user->id,
                'email'      => $user->email,
                'ip_address' => request()->ip(),
                'status'     => $status,
            ]);

            app(\App\Services\SecurityIncidentService::class)->handle($log);
        } else {
            // Log failed attempts for non-existent users
            $log = LoginLog::create([
                'user_id'    => null,
                'email'      => $submittedEmail,
                'ip_address' => request()->ip(),
                'status'     => 'failed',
            ]);

            app(\App\Services\SecurityIncidentService::class)->handle($log);
        }
    }
}
