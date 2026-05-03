<x-layouts::app :title="__('Security Dashboard')">
    <div class="flex flex-col gap-6">

        <!-- Top Metrics Cards -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">

            <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="size-5 text-emerald-500" />
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Successful Logins</h3>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['my_successful_logins'] }}</p>
            </div>

            <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon.x-circle class="size-5 text-red-500" />
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Failed Attempts</h3>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['my_failed_logins'] }}</p>
            </div>

            <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon.shield-exclamation class="size-5 text-orange-500" />
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">High Risk Detected</h3>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['global_high_risk'] }}</p>
            </div>

            <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon.lock-closed class="size-5 text-indigo-500" />
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Lock Status</h3>
                </div>
                <p class="mt-2 text-xl font-bold">
                    @if($metrics['user_is_locked'])
                        <span class="text-red-500 italic">LOCKED</span>
                    @else
                        <span class="text-emerald-500 italic">SECURE</span>
                    @endif
                </p>
            </div>

        </div>

        {{-- User-only: Last Login + Account Protection --}}
        @if(!$isAdmin)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- 1. Last Login Summary --}}
                <div class="p-6 rounded-2xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 shadow-sm border-t-4 border-t-indigo-600">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:icon.clock class="size-5 text-indigo-500" />
                        <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase italic tracking-tight">{{ __('Last Login Summary') }}</h2>
                    </div>

                    @if($last_login)
                        <div class="space-y-3">
                            <div class="grid grid-cols-[1fr_auto] items-center gap-4 text-xs">
                                <span class="text-zinc-500">{{ __('Verified Time') }}</span>
                                <span class="font-bold text-zinc-900 dark:text-white">{{ $last_login->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="grid grid-cols-[1fr_auto] items-center gap-4 text-xs">
                                <span class="text-zinc-500">{{ __('Location / IP') }}</span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-white">{{ $last_login->ip_address }}</span>
                            </div>
                            <div class="grid grid-cols-[1fr_auto] items-center gap-4 text-xs">
                                <span class="text-zinc-500">{{ __('Method Used') }}</span>
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <flux:icon :icon="$last_login->login_method === 'passkey' ? 'finger-print' : 'key'" class="size-3 text-zinc-400" />
                                    <span class="font-black uppercase text-[10px] text-zinc-600 dark:text-zinc-400 truncate">{{ $last_login->login_method }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-[1fr_auto] items-center gap-4 text-xs">
                                <span class="text-zinc-500">{{ __('Integrity Status') }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase border border-emerald-200">Verified</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic py-4 text-center">{{ __('No successful login history found.') }}</p>
                    @endif

                    <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <flux:button href="{{ route('security-logs') }}" variant="ghost" size="xs" icon-trailing="chevron-right" class="w-full">
                            {{ __('View Full Audit Trail') }}
                        </flux:button>
                    </div>
                </div>

                {{-- 2. Account Protection Actions --}}
                <div class="p-6 rounded-2xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 shadow-sm border-t-4 border-t-red-600">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:icon.bolt class="size-5 text-red-500" />
                        <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase italic tracking-tight">{{ __('Account Protection') }}</h2>
                    </div>

                    <p class="text-xs text-zinc-500 mb-6">{{ __('Immediate defensive triggers to secure your identity in case of suspected compromise.') }}</p>

                    <div class="flex flex-col gap-3">
                        <form action="{{ route('security.report') }}" method="POST" onsubmit="return confirm('Immediately lock your account and report suspicious activity?')">
                            @csrf
                            <flux:button type="submit" variant="subtle" size="sm" class="w-full justify-start text-red-600 hover:bg-red-50">
                                <flux:icon.shield-exclamation class="size-3 mr-2" /> {{ __('This wasn\'t me') }}
                            </flux:button>
                        </form>

                        <form action="{{ route('security.lock') }}" method="POST" onsubmit="return confirm('Immediately lock your account? You will need administrative assistance or a verified recovery method to unlock.')">
                            @csrf
                            <flux:button type="submit" variant="ghost" size="sm" class="w-full justify-start text-orange-600">
                                <flux:icon.lock-closed class="size-3 mr-2" /> {{ __('Lock my account') }}
                            </flux:button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- Admin: Security Command Center shortcut --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-8 dark:border-neutral-700 dark:bg-zinc-900 shadow-xl shadow-zinc-900/5 flex flex-col items-center text-center">
                <flux:icon.shield-check class="size-16 text-indigo-500 mb-4" />
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase italic">{{ __('Security Integrity Verified') }}</h2>
                <p class="text-zinc-500 max-w-lg mt-2 mb-8">{{ __('Your administrative account is protected by enterprise-grade risk monitoring and biometric authentication traces.') }}</p>
                <flux:button href="{{ route('security-logs') }}" variant="filled" size="base" icon-trailing="chevron-right" class="px-8 py-4">
                    {{ __('Open Security Command Center') }}
                </flux:button>
            </div>
        @endif

        {{-- Passkey / Security Key Registration — visible to ALL roles --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-emerald-950/40 dark:via-zinc-900 dark:to-teal-950/30 shadow-lg shadow-emerald-100/50 dark:shadow-emerald-900/20 p-8">

            {{-- Decorative glow blobs --}}
            <div class="pointer-events-none absolute -top-10 -right-10 size-48 rounded-full bg-emerald-400/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-10 -left-10 size-48 rounded-full bg-teal-400/10 blur-3xl"></div>

            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                {{-- Left: Icon + text --}}
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center size-14 rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-500/40">
                        <flux:icon.finger-print class="size-8 text-white" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-base font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ __('Passkey / Security Keys') }}</h2>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider">Recommended</span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 max-w-md">
                            {{ __('Register a biometric authenticator (fingerprint, FaceID) or hardware security key for fast, passwordless login. More secure than passwords.') }}
                        </p>
                        <p id="passkey-status" class="text-xs font-semibold mt-2"></p>
                    </div>
                </div>

                {{-- Right: CTA button --}}
                <div class="flex-shrink-0">
                    <flux:button type="button" id="register-passkey-btn" variant="filled" size="base" class="shadow-md shadow-emerald-500/30 whitespace-nowrap">
                        <flux:icon.finger-print class="size-4 mr-2" /> {{ __('Register a Passkey') }}
                    </flux:button>
                </div>
            </div>
        </div>

    </div>

    <script src="{{ asset('js/webauthn.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn    = document.getElementById('register-passkey-btn');
            const status = document.getElementById('passkey-status');
            if (!btn) return;

            btn.addEventListener('click', function () {
                const token = document.querySelector('meta[name="csrf-token"]')?.content ?? null;
                let webauthn;
                try {
                    webauthn = new WebAuthn(
                        { registerOptions: '/webauthn/register/options', register: '/webauthn/register' },
                        {}, false, token
                    );
                } catch (e) {
                    status.className = 'text-xs font-semibold mt-2 text-red-600';
                    status.textContent = 'Error: ' + e.message;
                    return;
                }

                btn.disabled = true;
                status.className = 'text-xs font-semibold mt-2 text-zinc-500';
                status.textContent = 'Waiting for your device…';

                webauthn.register()
                    .then(() => {
                        status.className = 'text-xs font-semibold mt-2 text-emerald-600';
                        status.textContent = '✓ Passkey registered! You can now use it to log in.';
                        btn.disabled = false;
                    })
                    .catch(async (error) => {
                        btn.disabled = false;
                        if (error?.name === 'NotAllowedError') { status.textContent = ''; return; }
                        let msg = 'Registration failed.';
                        if (error instanceof Error) msg += ' (' + error.name + ': ' + error.message + ')';
                        else if (error?.json) {
                            try { const e = await error.json(); if (e.message) msg = e.message; } catch(_) {}
                        }
                        status.className = 'text-xs font-semibold mt-2 text-red-600';
                        status.textContent = msg;
                    });
            });
        });
    </script>
</x-layouts::app>