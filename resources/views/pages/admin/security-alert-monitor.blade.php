<?php

use App\Models\LoginLog;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $lastCheck;

    public function mount()
    {
        // Use the session to persist the last check timestamp across page navigations.
        // This prevents "flooding" the admin with the same alerts every time they click a menu item.
        $this->lastCheck = session('last_security_alert_check');

        if (! $this->lastCheck) {
            // Very first check of the session: look back 5 minutes
            $this->lastCheck = now()->subMinutes(5)->toDateTimeString();
        }

        $this->checkNewLocks();
    }

    public function checkNewLocks()
    {
        if (Auth::user()?->role !== 'admin') {
            return;
        }

        $newLockedLogs = LoginLog::where('status', 'locked')
            ->where('created_at', '>', $this->lastCheck)
            ->with('user')
            ->oldest() // Process from oldest to newest for chronological toasts
            ->get();

        foreach ($newLockedLogs as $log) {
            $email = $log->user ? $log->user->email : 'Unknown Account';
            
            \Flux\Flux::toast(
                variant: 'danger',
                heading: __('Security Alert: Account Locked'),
                text: __('The account :email has been restricted due to suspicious login activity.', ['email' => $email]),
            );
        }

        // Update last check to the latest detected log OR now
        $this->lastCheck = $newLockedLogs->last()?->created_at?->toDateTimeString() ?? now()->toDateTimeString();
        
        session(['last_security_alert_check' => $this->lastCheck]);
    }
}; ?>

<div wire:poll.15s="checkNewLocks"></div>
