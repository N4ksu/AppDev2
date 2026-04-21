<?php

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('User Permissions')] class extends Component {
    public function toggleRole(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Prevent admins from demoting themselves (to avoid lock-out)
        if ($user->id === auth()->id()) {
            Flux::toast(variant: 'danger', text: __('You cannot change your own role.'));
            return;
        }

        $user->role = ($user->role === 'admin') ? 'user' : 'admin';
        $user->save();

        Flux::toast(
            variant: 'success', 
            text: __('Role for :email updated to :role.', [
                'email' => $user->email, 
                'role' => strtoupper($user->role)
            ])
        );
    }

    public function with(): array
    {
        return [
            'users' => User::latest()->get(),
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('User Permissions') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Manage administrative access and roles for all registered accounts.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('User / Identity') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Current Role') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Protection Status') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach($users as $user)
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
                            <td class="px-6 py-4">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-bold text-purple-700 ring-1 ring-inset ring-purple-700/10 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/30">
                                        <flux:icon.shield-check class="mr-1 size-3" /> {{ __('ADMIN') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-white/5 dark:text-zinc-400 dark:ring-white/10">
                                        {{ __('STANDARD USER') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_locked && ($user->locked_until === null || now()->lessThan($user->locked_until)))
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                                        <flux:icon.lock-closed class="size-3" /> {{ __('LOCKED') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                        <flux:icon.shield-check class="size-3" /> {{ __('ACTIVE') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button 
                                    variant="ghost" 
                                    size="sm" 
                                    icon="{{ $user->role === 'admin' ? 'user' : 'shield-check' }}" 
                                    wire:click="toggleRole({{ $user->id }})"
                                    wire:confirm="{{ $user->role === 'admin' ? __('Are you sure you want to revoke admin access for this user?') : __('Are you sure you want to grant admin access to this user?') }}"
                                    :disabled="$user->id === auth()->id()"
                                >
                                    {{ $user->role === 'admin' ? __('Revoke Admin Access') : __('Grant Admin Access') }}
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
