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
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in with Password') }}
                </flux:button>
                <div class="relative flex items-center">
                    <div class="flex-grow border-t border-zinc-200 dark:border-zinc-700"></div>
                    <span class="shrink-0 px-2 text-sm text-zinc-500 dark:text-zinc-400">or</span>
                    <div class="flex-grow border-t border-zinc-200 dark:border-zinc-700"></div>
                </div>
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <flux:button type="button" onclick="loginWithPasskey()" class="w-full">
                    <flux:icon.finger-print class="size-5 mr-2" /> {{ __('Log in with Passkey') }}
                </flux:button>
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
