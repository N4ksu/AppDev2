<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        $userId = $user->id;

        $data = [
            'metrics' => [
                'my_successful_logins' => \App\Models\LoginLog::where('user_id', $userId)->where('status', 'success')->count(),
                'my_failed_logins'     => \App\Models\LoginLog::where('user_id', $userId)->where('status', 'failed')->count(),
                'my_total_sessions'    => \App\Models\LoginLog::where('user_id', $userId)->count(),
                'user_is_locked'       => $user->is_locked && ($user->locked_until === null || now()->lessThan($user->locked_until)),
            ],
            'recent_logs' => \App\Models\LoginLog::where('user_id', $userId)->latest()->take(10)->get(),
        ];

        if ($user->role === 'admin') {
            $data['recent_incidents'] = \App\Models\SecurityIncident::where('status', 'open')->latest()->take(5)->get();
            $data['critical_incidents'] = \App\Models\SecurityIncident::where('severity', 'critical')->where('status', '!=', 'resolved')->latest()->take(5)->get();
            $data['resolved_incidents'] = \App\Models\SecurityIncident::where('status', 'resolved')->latest()->take(5)->get();
        }

        return view('dashboard', $data);
    })->name('dashboard');

    Route::livewire('security-logs', 'pages::security-logs')->name('security-logs');

    // Administrative Security Management
    Route::middleware([EnsureUserHasRole::class . ':admin'])->group(function () {
        Route::livewire('admin/security-incidents', 'pages::admin.security-incidents')->name('admin.security-incidents');
        Route::livewire('admin/security-settings', 'pages::admin.security-settings')->name('admin.security-settings');
        Route::livewire('admin/account-unlocks', 'pages::admin.account-unlocks')->name('admin.account-unlocks');
    });
});

require __DIR__.'/settings.php';
