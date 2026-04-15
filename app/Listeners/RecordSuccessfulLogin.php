<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;
use App\Models\User;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user?->getAuthIdentifier(),
            'ip_address' => request()->ip(),
            'status' => 'success'
        ]);

        if ($event->user) {
            $user = User::find($event->user->getAuthIdentifier());
            if ($user && ($user->failed_attempts > 0 || $user->is_locked)) {
                $user->failed_attempts = 0;
                $user->is_locked = false;
                $user->locked_until = null;
                $user->save();
            }
        }
    }
}
