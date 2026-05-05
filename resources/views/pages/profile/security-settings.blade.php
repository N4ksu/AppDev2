<x-layouts::app :title="__('Security Settings')">
    <div class="px-4 py-6 sm:px-0">
        <flux:heading size="xl">{{ __('Security Settings') }}</flux:heading>
        <flux:subheading>{{ __('Manage your passwordless authentication and social accounts.') }}</flux:subheading>

        <flux:separator class="my-6" />

        <div class="space-y-6">
            {{-- Passkey Management --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Passwordless Passkeys') }}</flux:heading>
                        <flux:subheading>{{ __('Use biometrics like FaceID or Fingerprint to sign in securely without a password.') }}</flux:subheading>
                    </div>
                    <flux:icon.finger-print class="size-10 text-zinc-400" />
                </div>

                <div class="mt-6">
                    @if(auth()->user()->webauthnCredentials()->count() > 0)
                        <div class="space-y-3">
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Registered Devices:') }}</p>
                            @foreach(auth()->user()->webauthnCredentials as $credential)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.device-phone-mobile class="size-5 text-zinc-500" />
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $credential->name ?: __('Unknown Device') }}</p>
                                            <p class="text-xs text-zinc-500">{{ __('Added on') }} {{ $credential->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 text-center">
                            <flux:icon.shield-exclamation class="size-8 mx-auto text-zinc-400 mb-2" />
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('You haven\'t registered any passkeys yet.') }}</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <flux:button variant="filled" onclick="registerPasskey()">
                            <flux:icon.plus class="size-4 mr-2" /> {{ __('Register New Passkey') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- Google Account --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Google Connection') }}</flux:heading>
                        <flux:subheading>{{ __('Link your Google account for one-click access.') }}</flux:subheading>
                    </div>
                    <svg class="size-10" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    @if(auth()->user()->google_id)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 grow">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" class="size-10 rounded-full border border-white shadow-sm" alt="Avatar">
                            @else
                                <flux:icon.user-circle class="size-10 text-indigo-400" />
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">{{ auth()->user()->display_name ?: auth()->user()->name }}</p>
                                <p class="text-xs text-indigo-700 dark:text-indigo-300">{{ __('Linked with Google') }}</p>
                            </div>
                        </div>
                    @else
                        <flux:button variant="outline" :href="route('auth.google')" class="grow py-3">
                            {{ __('Connect Google Account') }}
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    <script src="/js/webauthn.js"></script>
    <script>
        function registerPasskey() {
            const name = prompt("Enter a name for this device (e.g., iPhone, Macbook):", "My Device");
            if (!name) return;

            const webauthn = new WebAuthn({
                registerOptions: '/webauthn/register/options',
                register: '/webauthn/register'
            });

            webauthn.register({ name: name }).then(() => {
                alert("Passkey registered successfully!");
                window.location.reload();
            }).catch(error => {
                console.error(error);
                alert("Failed to register passkey: " + error.message);
            });
        }
    </script>
</x-layouts::app>
