<?php

use Livewire\Component;
use App\Models\SecurityIncident;
use App\Models\LoginLog;
use App\Models\User;

new class extends Component {
    public function with()
    {
        return [
            'admin_metrics' => [
                'open_incidents' => SecurityIncident::where('status', '!=', 'resolved')->count(),
                'high_risk_incidents' => SecurityIncident::whereIn('severity', ['high', 'critical'])->where('status', '!=', 'resolved')->count(),
                'enumeration_attacks' => SecurityIncident::where('type', 'enumeration')->where('status', '!=', 'resolved')->count(),
                'locked_today' => User::where('is_locked', true)->where('updated_at', '>=', now()->startOfDay())->count(),
                'failed_today' => LoginLog::where('status', '!=', 'success')->where('created_at', '>=', now()->startOfDay())->count(),
                'most_targeted' => LoginLog::where('status', '!=', 'success')->select('email')->groupBy('email')->orderByRaw('COUNT(*) DESC')->first()?->email ?? 'None',
                'most_suspicious_ip' => LoginLog::where('status', '!=', 'success')->select('ip_address')->groupBy('ip_address')->orderByRaw('COUNT(*) DESC')->first()?->ip_address ?? 'None',
            ]
        ];
    }
} ?>

<div class="flex flex-col gap-6" wire:poll.10s>
    <!-- Admin Primary Metrics Grid -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2">
                <flux:icon.shield-exclamation class="size-5 text-orange-500" />
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Open Incidents</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $admin_metrics['open_incidents'] }}</p>
        </div>

        <div class="flex flex-col rounded-xl border border-red-500/20 bg-red-500/5 p-6 dark:border-red-500/30 dark:bg-red-500/5 shadow-sm">
            <div class="flex items-center gap-2">
                <flux:icon.fire class="size-5 text-red-600 dark:text-red-500" />
                <h3 class="text-sm font-medium text-red-600 dark:text-red-400">High Risk Threats</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-red-700 dark:text-red-400">{{ $admin_metrics['high_risk_incidents'] }}</p>
        </div>

        <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2">
                <flux:icon.magnifying-glass-circle class="size-5 text-indigo-500" />
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Enumeration Campaigns</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $admin_metrics['enumeration_attacks'] }}</p>
        </div>

        <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2">
                <flux:icon.user-minus class="size-5 text-zinc-500" />
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Locked Accounts Today</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $admin_metrics['locked_today'] }}</p>
        </div>
    </div>

    <!-- Secondary Admin Metrics (Targeting info) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <flux:icon.exclamation-triangle class="size-4 text-orange-500" />
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Failed Attempts Today</span>
            </div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $admin_metrics['failed_today'] }}</div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <flux:icon.identification class="size-4 text-indigo-500" />
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Most Targeted Identity</span>
            </div>
            <div class="text-lg font-bold text-zinc-900 dark:text-white truncate" title="{{ $admin_metrics['most_targeted'] }}">
                {{ $admin_metrics['most_targeted'] }}
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <flux:icon.globe-alt class="size-4 text-red-500" />
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Most Suspicious IP</span>
            </div>
            <div class="text-lg font-bold text-zinc-900 dark:text-white">{{ $admin_metrics['most_suspicious_ip'] }}</div>
        </div>
    </div>
</div>
