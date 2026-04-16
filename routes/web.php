<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $userId = auth()->id();

        return view('dashboard', [
            'metrics' => [
                // All counts are scoped to the current user only
                'my_successful_logins' => \App\Models\LoginLog::where('user_id', $userId)->where('status', 'success')->count(),
                'my_failed_logins'     => \App\Models\LoginLog::where('user_id', $userId)->where('status', 'failed')->count(),
                'my_total_sessions'    => \App\Models\LoginLog::where('user_id', $userId)->count(),
                // Current live lock state — from the user record, not log counts
                'user_is_locked'       => auth()->user()->is_locked && (auth()->user()->locked_until === null || now()->lessThan(auth()->user()->locked_until)),
            ],
            // Recent activity: only this user's own login events
            'recent_logs' => \App\Models\LoginLog::where('user_id', $userId)->latest()->take(10)->get(),
        ]);
    })->name('dashboard');

    Route::livewire('security-logs', 'pages::security-logs')->name('security-logs');

    // Administrative Security Management
    Route::middleware([EnsureUserHasRole::class . ':admin'])->group(function () {
        Route::livewire('admin/security-settings', 'pages::admin.security-settings')->name('admin.security-settings');
        Route::livewire('admin/account-unlocks', 'pages::admin.account-unlocks')->name('admin.account-unlocks');
    });
});

require __DIR__.'/settings.php';
