<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthn\WebAuthnLoginController;
use App\Http\Controllers\WebAuthn\WebAuthnRegisterController;
use App\Http\Controllers\SecurityActionController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';

        $query = \App\Models\LoginLog::query();
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Fetch last successful login
        $lastLogin = null;
        if (!$isAdmin) {
            $lastLogin = \App\Models\LoginLog::where('user_id', $user->id)
                ->where('status', 'success')
                ->latest()
                ->first();
        }

        return view('dashboard', [
            'metrics' => [
                'my_successful_logins' => \App\Models\LoginLog::where('user_id', $user->id)->where('status', 'success')->count(),
                'my_failed_logins'     => \App\Models\LoginLog::where('user_id', $user->id)->where('status', 'failed')->count(),
                'my_total_sessions'    => \App\Models\LoginLog::where('user_id', $user->id)->count(),
                'user_is_locked'       => $user->is_locked && ($user->locked_until === null || now()->lessThan($user->locked_until)),
                'global_high_risk'     => $isAdmin ? \App\Models\LoginLog::where('risk_level', 'high_risk')->count() : 0,
            ],
            'recent_logs' => $query->latest()->take(15)->get(),
            'last_login' => $lastLogin,
            'isAdmin' => $isAdmin,
        ]);
    })->name('dashboard');

    Route::livewire('security-logs', 'pages::security-logs')->name('security-logs');

    // Centralized Security Actions
    Route::post('security/lock', [SecurityActionController::class, 'lockAccount'])->name('security.lock');
    Route::post('security/report', [SecurityActionController::class, 'reportActivity'])->name('security.report');

    // Administrative Security Management
    Route::middleware([EnsureUserHasRole::class . ':admin'])->group(function () {
        Route::livewire('admin/security-settings', 'pages::admin.security-settings')->name('admin.security-settings');
        Route::livewire('admin/account-unlocks', 'pages::admin.account-unlocks')->name('admin.account-unlocks');
        Route::livewire('admin/user-permissions', 'pages::admin.user-permissions')->name('admin.user-permissions');
        
        Route::post('/admin/toggle-chat', [\App\Http\Controllers\AdminController::class, 'toggleChat'])->name('admin.toggle-chat');
    });

    // Security Chat (Scoped by role internally)
    Route::livewire('security-chat', 'pages::security-chat')->name('security-chat');
    Route::post('security-chat/send', \App\Http\Controllers\SecurityChatController::class)->name('security-chat.send');
    Route::get('api/security-chat/history', [\App\Http\Controllers\SecurityChatController::class, 'history'])->name('security-chat.history');
    Route::delete('api/security-chat/clear', [\App\Http\Controllers\SecurityChatController::class, 'clear'])->name('security-chat.clear');

    // User Profile & Security Settings (where passkeys are managed)
    Route::get('profile/security', function() {
        return view('pages.profile.security-settings');
    })->name('profile.security');
});

Route::middleware(['guest'])->group(function () {
    Route::get('auth/google', [App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::post('webauthn/login/options', [WebAuthnLoginController::class, 'options']);
    Route::post('webauthn/login', [WebAuthnLoginController::class, 'login'])->name('webauthn.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('webauthn/register/options', [WebAuthnRegisterController::class, 'options']);
    Route::post('webauthn/register', [WebAuthnRegisterController::class, 'register'])->name('webauthn.register');
});

require __DIR__.'/settings.php';
