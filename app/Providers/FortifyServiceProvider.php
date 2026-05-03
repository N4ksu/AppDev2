<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            function () {
                return new class implements \Laravel\Fortify\Contracts\RegisterResponse {
                    public function toResponse($request)
                    {
                        // Fortify auto-logs in the user. This logs them right back out.
                        \Illuminate\Support\Facades\Auth::logout();
                        request()->session()->invalidate();
                        request()->session()->regenerateToken();
                        
                        return redirect()->route('login')->with('status', 'Registration successful! Please log in with your new credentials.');
                    }
                };
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $ipBlockService = app(\App\Services\IPBlockService::class);
            $riskService = app(\App\Services\RiskScoringService::class);

            // 1. Check if IP is temporarily blocked
            if ($ipBlockService->isBlocked($request->ip())) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Access temporarily restricted from this IP due to suspicious activity.',
                ]);
            }

            $user = \App\Models\User::where('email', $request->email)->first();

            // 2. Early Risk Assessment (Even before password check)
            // We check for brute force or identity guessing patterns
            $risk = $riskService->calculateRisk($user, $request, 'password', false, $request->email);

            if ($risk['score'] >= 70) {
                // Deny login immediately
                \App\Models\LoginLog::create([
                    'user_id' => $user?->id,
                    'email' => $request->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'failed',
                    'action' => 'login',
                    'login_method' => 'password',
                    'risk_score' => $risk['score'],
                    'risk_level' => $risk['level'],
                    'action_taken' => 'denied', // STRICT MITIGATION
                ]);

                // Optionally block IP if risk is extreme or repeated
                if ($risk['score'] >= 90) {
                    $ipBlockService->block($request->ip(), 10); // 10 minute block
                }

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Security Threat Detected: Access Denied. Your activity has been logged for forensic review.',
                ]);
            }

            if ($user && $user->is_locked) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Your account has been restricted for security reasons. Please contact an administrator.',
                ]);
            }

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                session(['login_method' => 'password']); // For successful login listener
                return $user;
            }

            return false;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
