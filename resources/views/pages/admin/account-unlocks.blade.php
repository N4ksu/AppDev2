<?php

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Locked Accounts')] class extends Component {
    public $lockedUsers = [];

    public function mount(): void
    {
        $this->loadLockedUsers();
    }

    public function unlock(int $userId): void
    {
        $user = User::findOrFail($userId);

        $user->is_locked = false;
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->save();

        \App\Models\LoginLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success',
            'action' => 'account_unlock',
            'login_method' => 'admin_action',
            'risk_score' => 0,
            'risk_level' => 'safe',
            'action_taken' => 'allowed',
        ]);

        $this->loadLockedUsers();

        Flux::toast(variant: 'success', text: __('Access restored for :email.', ['email' => $user->email]));
    }

    public function loadLockedUsers(): void
    {
        // Load all users that are currently restricted.
        // We no longer auto-cleanup expired locks; all unlocks must be manual by an administrator.
        $this->lockedUsers = User::where('is_locked', true)
            ->latest()
            ->get();
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Locked Accounts') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Review and restore access for accounts restricted by the security monitor or self-locked by users. Access can only be restored manually.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('User / Identity') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Failed Attempts') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Restricted Until') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($lockedUsers as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                    <div class="flex flex-col">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                <span
                                    class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-400/20">
                                    {{ $user->failed_attempts }} Attempts
                                </span>
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 font-mono">
                                {{ $user->locked_until?->format('Y-m-d H:i:s') ?? 'Manual Lock' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button variant="ghost" size="sm" icon="key" wire:click="unlock({{ $user->id }})"
                                    wire:confirm="{{ __('Are you sure you want to restore access for this user?') }}">
                                    {{ __('Restore Access') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 italic">
                                {{ __('No accounts are currently restricted by the security monitor.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>