<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\LoginLog;
use App\Models\User;

class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        LoginLog::create([
            'user_id' => $event->user?->getAuthIdentifier(),
            'ip_address' => request()->ip(),
            'status' => 'failed'
        ]);

        if ($event->user) {
            $user = User::find($event->user->getAuthIdentifier());
            if ($user) {
                $user->failed_attempts += 1;
                
                if ($user->failed_attempts >= 3) {
                    $user->is_locked = true;
                    $user->locked_until = now()->addMinutes(15);
                }
                $user->save();
            }
        }
    }
}
