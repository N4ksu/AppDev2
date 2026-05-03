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
            'pageTitle' => $isAdmin ? __('Identity & Access Management (IAM) Audit') : __('Your Security Event Log'),
            'pageSubtitle' => $isAdmin
                ? __('Critical monitoring of authentication success, biometric registrations, account lockouts, and administrative restorations.')
                : __('Personal security trail including passkey updates, login verifications, and reactive account protection events.'),
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <div class="relative mb-2 w-full text-center lg:text-left">
        <flux:heading size="xl" level="1" class="!text-indigo-600 dark:!text-indigo-400 font-black tracking-tight">
            {{ $pageTitle }}
        </flux:heading>
        <flux:subheading size="lg" class="max-w-3xl">
            {{ $pageSubtitle }}
        </flux:subheading>
    </div>

    {{-- Filter Bar --}}
    <div
        class="flex flex-col lg:flex-row flex-wrap gap-4 p-4 rounded-xl border border-indigo-100 bg-indigo-50/10 dark:border-indigo-900/30 dark:bg-zinc-900 shadow-sm">
        {{-- Filter Bar --}}
        <div
            class="flex flex-col lg:flex-row flex-wrap gap-4 p-4 rounded-xl border border-indigo-100 bg-indigo-50/10 dark:border-indigo-900/30 dark:bg-zinc-900 shadow-sm">

            {{-- 1. Security Activity Type --}}
            <div class="w-full lg:w-48">
                <flux:select wire:model.live="status" :label="__('Outcome View')">
                    <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
                    <flux:select.option value="success">{{ __('Verified Only') }}</flux:select.option>
                    <flux:select.option value="failed">{{ __('Blocked Only') }}</flux:select.option>
                </flux:select>
            </div>

            {{-- 2. Audit Source (Admin Only) --}}
            @if($isAdminView)
                <div class="w-full lg:w-48">
                    <flux:select wire:model.live="identity_type" :label="__('Source Identity')">
                        <flux:select.option value="">{{ __('All Sources') }}</flux:select.option>
                        <flux:select.option value="registered">{{ __('Registered User') }}</flux:select.option>
                        <flux:select.option value="guest">{{ __('Unrecognized Identity') }}</flux:select.option>
                    </flux:select>
                </div>

                {{-- 3. Identity Search (Admin Only) --}}
                <div class="flex-1 min-w-[300px]">
                    <flux:input wire:model.live.debounce.300ms="search" icon="shield-check" :label="__('Target Account')"
                        placeholder="{{ __('Search email or unique identifier...') }}" />
                </div>
            @endif

            {{-- 4. Audit Window --}}
            <div class="w-full lg:w-80 flex gap-2 items-end">
                <div class="flex-1">
                    <flux:input wire:model.live="date_from" type="date" :label="__('Filter From')" />
                </div>
                <div class="flex-1">
                    <flux:input wire:model.live="date_to" type="date" :label="__('Filter To')" />
                </div>
            </div>

            {{-- 5. Reset --}}
            <div class="flex items-end">
                <flux:button wire:click="clearFilters" variant="ghost" icon="arrow-path" class="mb-0.5 ml-auto">
                    {{ __('Reset') }}
                </flux:button>
            </div>
        </div>

        <div
            class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 shadow-xl shadow-zinc-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="{{ $isAdminView ? 'bg-zinc-100 text-zinc-500' : 'bg-indigo-50 text-indigo-700' }} dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">
                                {{ $isAdminView ? __('Identity') : __('Device / Context') }}
                            </th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">
                                {{ $isAdminView ? __('Event / Type') : __('Activity') }}
                            </th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">
                                {{ __('Security Risk') }}
                            </th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">
                                {{ $isAdminView ? __('Mitigation Response') : __('System Status') }}
                            </th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">
                                {{ $isAdminView ? __('Source Node') : __('Location / IP') }}
                            </th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] text-right">
                                {{ __('Timestamp') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        @if(!$isAdminView)
                                            {{-- Normal User Logic: Focus on Device/Method --}}
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-zinc-900 dark:text-white truncate max-w-[150px]">
                                                    {{ $log->login_method === 'passkey' ? 'Trusted Bio-Device' : ($log->login_method === 'password' ? 'Verified Session' : 'Security Action') }}
                                                </span>
                                                @if($log->status === 'success')
                                                    <flux:icon.shield-check class="size-3 text-emerald-500" />
                                                @else
                                                    <flux:icon.exclamation-triangle class="size-3 text-amber-500" />
                                                @endif
                                            </div>
                                        @elseif($log->user && $log->status === 'success')
                                            {{-- Admin Case 1: Authenticated Session --}}
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-zinc-900 dark:text-white truncate max-w-[150px]">
                                                    {{ $log->user->name }}
                                                </span>
                                                <flux:icon.shield-check class="size-3 text-indigo-500" />
                                            </div>
                                        @elseif($log->email && \App\Models\User::where('email', $log->email)->exists())
                                            {{-- Admin Case 2: Known Account (Failed or Suspicious) --}}
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-zinc-900 dark:text-white truncate max-w-[150px]">
                                                    Known User
                                                </span>
                                                <span
                                                    class="px-1.5 py-0.5 rounded-full bg-orange-50 text-orange-600 text-[8px] font-black uppercase tracking-tighter border border-orange-200">VALID
                                                    IDENTITY</span>
                                            </div>
                                        @else
                                            {{-- Admin Case 3: Unknown / Guest --}}
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-zinc-400 italic">Guest / Unknown User</span>
                                                <span
                                                    class="px-1.5 py-0.5 rounded-full bg-zinc-100 text-zinc-500 text-[8px] font-black uppercase tracking-tighter border border-zinc-300">UNTRUSTED</span>
                                            </div>
                                        @endif
                                        <div class="text-[10px] text-zinc-500 font-mono tracking-tighter">{{ $log->email }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-[11px] font-black uppercase text-indigo-700 dark:text-indigo-400 tracking-tight">
                                            {{ str_replace('_', ' ', $log->action) }}
                                        </span>
                                        <div class="flex items-center gap-1 text-[9px] text-zinc-400">
                                            <flux:icon.{{ $log->login_method === 'passkey' ? 'finger-print' : 'key' }}
                                                class="size-2.5" />
                                            {{ strtoupper($log->login_method) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                                            <div class="h-full {{ $log->risk_score >= 70 ? 'bg-red-500' : ($log->risk_score >= 40 ? 'bg-orange-500' : 'bg-emerald-500') }}"
                                                style="width: {{ $log->risk_score }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-black uppercase tracking-tighter {{ $log->risk_score >= 70 ? 'text-red-600' : ($log->risk_score >= 40 ? 'text-orange-600' : 'text-emerald-600') }}">
                                            {{ $log->risk_level === 'high_risk' ? 'High' : ($log->risk_level === 'suspicious' ? 'Suspicious' : 'Safe') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold font-mono">
                                    @php
                                        $actionTaken = $log->action_taken;

                                        // Logic for older logs or missing action_taken
                                        if (!$actionTaken) {
                                            if ($log->status === 'success') {
                                                $actionTaken = 'verified';
                                            } elseif ($log->risk_score < 40) {
                                                $actionTaken = 'allowed'; // Safe but credentials failed
                                            } else {
                                                $actionTaken = 'denied';
                                            }
                                        }

                                        $bgColor = match ($actionTaken) {
                                            'allowed', 'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'denied' => 'bg-red-50 text-red-700 border-red-200',
                                            'flagged' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'locked' => 'bg-orange-50 text-orange-700 border-orange-200',
                                            default => 'bg-zinc-50 text-zinc-700 border-zinc-200'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded border uppercase text-[10px] {{ $bgColor }}">
                                        {{ $actionTaken }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] text-zinc-500 font-mono">{{ $log->ip_address }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-[11px] text-zinc-600 dark:text-zinc-400">
                                        {{ $log->created_at->format('Y-m-d H:i') }}
                                    </div>
                                    <div class="text-[9px] text-zinc-400">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-16 text-center text-zinc-500 dark:text-zinc-400 italic font-medium">
                                    {{ __('Zero authentication events recorded matching the current security filters.') }}
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