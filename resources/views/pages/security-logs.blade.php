<?php

use App\Models\LoginLog;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new #[Title('Security Logs')] class extends Component {
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $identity_type = '';

    #[Url]
    public string $date_from = '';

    #[Url]
    public string $date_to = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['status', 'identity_type', 'search', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function with(): array
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';

        $query = LoginLog::query();

        // 1. Privacy Scoping
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        } else {
            $query->with('user');
        }

        // 2. Security Result (Status) Filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // 3. Identity Type Filter (Registered vs Guest)
        if ($this->identity_type === 'registered') {
            $query->whereNotNull('user_id');
        } elseif ($this->identity_type === 'guest') {
            $query->whereNull('user_id');
        }

        // 4. Account Identity Search (Searches the explicit email column)
        if ($this->search) {
            $query->where('email', 'like', '%' . $this->search . '%');
        }

        // 5. Date Range Filter
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        return [
            'logs' => $query->latest()->paginate(15),
            'isAdminView' => $isAdmin,
            'pageTitle' => $isAdmin ? __('Full Security Audit Trail') : __('Your Security Activity'),
            'pageSubtitle' => $isAdmin 
                ? __('Perform system-wide auditing of login activities, identify suspicious patterns, and manage restricted access events.') 
                : __('Full history of your account access events for personal security monitoring and verification.'),
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <div class="relative mb-2 w-full">
        <flux:heading size="xl" level="1">{{ $pageTitle }}</flux:heading>
        <flux:subheading size="lg">
            {{ $pageSubtitle }}
        </flux:subheading>
    </div>

    {{-- Filter Bar --}}
    <div
        class="flex flex-col lg:flex-row flex-wrap gap-4 p-4 rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
        {{-- 1. Security Result --}}
        <div class="w-full lg:w-48">
            <flux:select wire:model.live="status" :label="__('Security Result')">
                <flux:select.option value="">{{ __('All Results') }}</flux:select.option>
                <flux:select.option value="success">{{ __('Login Success') }}</flux:select.option>
                <flux:select.option value="failed">{{ __('Login Failed') }}</flux:select.option>
                <flux:select.option value="locked">{{ __('Account Locked') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- 2. Identity Type (Admin Only) --}}
        @if($isAdminView)
            <div class="w-full lg:w-48">
                <flux:select wire:model.live="identity_type" :label="__('Identity Type')">
                    <flux:select.option value="">{{ __('All Identities') }}</flux:select.option>
                    <flux:select.option value="registered">{{ __('Registered User') }}</flux:select.option>
                    <flux:select.option value="guest">{{ __('Non-existent Account') }}</flux:select.option>
                </flux:select>
            </div>
        @endif

        {{-- 3. Account Identity (Admin Only) --}}
        @if($isAdminView)
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Account Identity')"
                    placeholder="{{ __('Search by email or account identity...') }}" />
            </div>
        @endif

        {{-- 4. Date From --}}
        <div class="w-full lg:w-44">
            <flux:input wire:model.live="date_from" type="date" :label="__('Date From')" />
        </div>

        {{-- 5. Date To --}}
        <div class="w-full lg:w-44">
            <flux:input wire:model.live="date_to" type="date" :label="__('Date To')" />
        </div>

        {{-- 6. Reset Filters --}}
        <div class="flex items-end">
            <flux:button wire:click="clearFilters" variant="ghost" class="mb-0.5">
                {{ __('Reset Filters') }}
            </flux:button>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Timestamp') }}</th>
                        @if($isAdminView)
                            <th class="px-6 py-4 font-medium">{{ __('Account Identity') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Identity Type') }}</th>
                        @endif
                        <th class="px-6 py-4 font-medium">{{ __('IP Source') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Security Result') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 font-mono">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            @if($isAdminView)
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ $log->email ?? ($log->user ? $log->user->email : ($log->user_id ? __('Deleted User') : __('Unknown Account'))) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->user_id)
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30">
                                            {{ __('Registered User') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-white/5 dark:text-zinc-400 dark:ring-white/10">
                                            {{ __('Non-existent Account') }}
                                        </span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $log->ip_address }}</td>
                            <td class="px-6 py-4">
                                @if($log->status === 'success')
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <flux:icon.check-circle class="size-3" /> {{ __('SUCCESS') }}
                                    </span>
                                @elseif($log->status === 'locked')
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-orange-500/10 px-2.5 py-1 text-xs font-semibold text-orange-600 dark:text-orange-400">
                                        <flux:icon.lock-closed class="size-3" /> {{ __('ACCOUNT LOCKED') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        <flux:icon.x-circle class="size-3" /> {{ __('LOGIN FAILED') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdminView ? 5 : 3 }}" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 italic">
                                {{ __('No security records match your current filters.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-6 border-t border-neutral-200 dark:border-neutral-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>