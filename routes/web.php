<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard', [
            'metrics' => [
                'successful_logins' => \App\Models\LoginLog::where('status', 'success')->count(),
                'failed_logins' => \App\Models\LoginLog::where('status', 'failed')->count(),
                'locked_accounts' => \App\Models\User::where('is_locked', true)->count(),
                'total_logs' => \App\Models\LoginLog::count(),
            ],
            'recent_logs' => \App\Models\LoginLog::with('user')->latest()->take(10)->get(),
        ]);
    })->name('dashboard');

    Route::get('security-logs', function () {
        return view('security-logs', [
            'logs' => \App\Models\LoginLog::with('user')->latest()->paginate(20),
        ]);
    })->name('security-logs');
});

require __DIR__.'/settings.php';
