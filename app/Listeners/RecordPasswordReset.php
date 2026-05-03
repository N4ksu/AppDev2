<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use App\Models\LoginLog;

class RecordPasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        LoginLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success',
            'action' => 'password_reset',
            'login_method' => 'password',
            'failed_attempts' => 0,
            'risk_score' => 0,
            'risk_level' => 'safe',
            'action_taken' => 'allowed',
        ]);
    }
}
