<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;
use App\Models\User;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $method = request()->is('webauthn/login*') ? 'passkey' : 'password';

        LoginLog::create([
            'user_id' => $user->getAuthIdentifier(),
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'status' => 'success',
            'login_method' => $method,
        ]);

        if ($user) {
            if ($user->failed_attempts > 0 || $user->is_locked) {
                $user->failed_attempts = 0;
                $user->is_locked = false;
                $user->locked_until = null;
                $user->save();
            }
        }
    }
}
