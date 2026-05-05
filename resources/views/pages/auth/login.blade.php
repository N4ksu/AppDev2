<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col items-center gap-2 text-center">
            <div class="flex items-center justify-center p-3 rounded-full bg-red-500/10 text-red-600 dark:text-red-400">
                <flux:icon.shield-exclamation variant="solid" class="size-8" />
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                Secure Account Access
            </h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Login activity is monitored for account protection. <br>
                <span class="text-red-600 dark:text-red-400 font-medium">Multiple failed attempts trigger a security lock to prevent bridge-force attacks.</span>
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            <div class="flex flex-col gap-3 mt-2">
                <flux:button variant="filled" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in with Password') }}
                </flux:button>
                <div class="relative flex items-center">
                    <div class="flex-grow border-t border-zinc-200 dark:border-zinc-700"></div>
                    <span class="shrink-0 px-2 text-sm text-zinc-500 dark:text-zinc-400">or</span>
                    <div class="flex-grow border-t border-zinc-200 dark:border-zinc-700"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <flux:button type="button" :href="route('auth.google')" class="w-full">
                        <svg class="size-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                        Google
                    </flux:button>
                    
                    <flux:button type="button" onclick="loginWithPasskey()" class="w-full">
                        <flux:icon.finger-print class="size-5 mr-2" /> Passkey
                    </flux:button>
                </div>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Create one') }}</flux:link>
            </div>
        @endif
    </div>

    <script src="/js/webauthn.js"></script>
    <script>
        function loginWithPasskey() {
            const emailInput = document.querySelector('input[name="email"]');
            const data = {};
            if (emailInput && emailInput.value) {
                data.email = emailInput.value;
            }
            
            const webauthn = new WebAuthn({
                 loginOptions: '/webauthn/login/options',
                 login: '/webauthn/login'
            });
            webauthn.login(data).then(() => {
                window.location.href = "{{ route('dashboard') }}";
            }).catch(async (error) => {
                if (error.name === 'NotAllowedError') return;
                let msg = 'Passkey authentication failed.';
                if (error && error.json) {
                    try { 
                        const err = await error.json(); 
                        if (err.errors && err.errors.email) {
                            msg = err.errors.email[0];
                        } else if (err.message) {
                            msg = err.message; 
                        }
                    } catch(e){}
                }
                if (error instanceof Error && msg === 'Passkey authentication failed.') {
                    msg += " (" + error.message + ")";
                }
                alert(msg);
            });
        }
    </script>
</x-layouts::auth>
