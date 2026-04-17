<x-layouts::app :title="auth()->user()->role === 'admin' ? __('Security Dashboard') : __('Dashboard')">
    <div class="flex flex-col gap-6">

    <div class="flex flex-col gap-8">

        @if(auth()->user()->role === 'admin')
            <div class="flex flex-col gap-6">
                <!-- Admin Security Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="xl" level="1">Security Command Center</flux:heading>
                        <flux:subheading italic>Real-time system-wide threat monitoring</flux:subheading>
                    </div>
                </div>

                <!-- Admin Security Metrics (Reactive) -->
                <livewire:admin.security-stats />

                <!-- Detailed Incident Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Critical Incidents -->
                    <div class="lg:col-span-1 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">Critical Alerts</flux:heading>
                            <flux:badge color="red" size="sm" inset="top">{{ count($critical_incidents) }}</flux:badge>
                        </div>
                        
                        <div class="flex flex-col gap-3">
                            @forelse($critical_incidents as $incident)
                                <div class="p-4 rounded-lg border-s-4 border-red-500 bg-white dark:bg-zinc-900 shadow-sm border border-neutral-200 dark:border-neutral-700">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold uppercase text-red-600 dark:text-red-400">{{ str_replace('_', ' ', $incident->type) }}</span>
                                        <span class="text-[10px] text-zinc-400 tabular-nums">{{ ($incident->last_detected_at ?? $incident->created_at)?->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white mb-1">
                                        {{ $incident->target_identifier ?? 'Multiple Targets' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        <flux:icon.globe-alt class="size-3" /> {{ $incident->source_ip }}
                                        <span class="mx-1">•</span>
                                        <flux:icon.document-text class="size-3" /> {{ $incident->logs_count }} events
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center border-2 border-dashed border-neutral-200 dark:border-neutral-700 rounded-xl text-zinc-500">
                                    No critical threats active.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Incidents -->
                    <div class="lg:col-span-2 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">Recent Security Incidents</flux:heading>
                            <flux:button size="sm" variant="ghost" :href="route('admin.security-incidents')" wire:navigate>View All</flux:button>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Incident</th>
                                            <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Severity</th>
                                            <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Target</th>
                                            <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Detected</th>
                                            <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                        @forelse($recent_incidents as $incident)
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="font-bold text-zinc-900 dark:text-white">{{ str_replace('_', ' ', ucfirst($incident->type)) }}</div>
                                                    <div class="text-[11px] text-zinc-500">{{ $incident->source_ip }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @php
                                                        $severityColor = match($incident->severity) {
                                                            'critical' => 'red',
                                                            'high' => 'orange',
                                                            'medium' => 'indigo',
                                                            default => 'zinc',
                                                        };
                                                    @endphp
                                                    <flux:badge :color="$severityColor" variant="solid" size="sm">{{ strtoupper($incident->severity) }}</flux:badge>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-zinc-600 dark:text-zinc-300">
                                                    {{ $incident->target_identifier ?? 'Distributed' }}
                                                </td>
                                                <td class="px-6 py-4 text-xs tabular-nums text-zinc-500 dark:text-zinc-400">
                                                    {{ ($incident->first_detected_at ?? $incident->created_at)?->format('M d, H:i') }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    @php
                                                        $statusIcon = match($incident->status) {
                                                            'open' => 'clock',
                                                            'investigating' => 'magnifying-glass-circle',
                                                            'resolved' => 'check-circle',
                                                            default => 'question-mark-circle',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 text-xs font-medium {{ $incident->status === 'open' ? 'text-orange-600' : 'text-zinc-500' }}">
                                                        <flux:icon :icon="$statusIcon" class="size-3.5" /> {{ ucfirst($incident->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">No security incidents detected. System is secure.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Standard User Dashboard UI -->
            <div class="flex flex-col gap-6">
                <!-- Top Metrics Cards -->
                <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                    <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.check-circle class="size-5 text-emerald-500" />
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Your Successful Logins</h3>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['my_successful_logins'] }}</p>
                    </div>

                    <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.x-circle class="size-5 text-red-500" />
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Your Failed Attempts</h3>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['my_failed_logins'] }}</p>
                    </div>

                    <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.lock-closed class="size-5 text-orange-500" />
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Status</h3>
                        </div>
                        <p class="mt-2 text-xl font-bold">
                            @if($metrics['user_is_locked'])
                                <span class="text-red-500 dark:text-red-400 italic">Locked</span>
                            @elseif(auth()->user()->failed_attempts > 0)
                                <span class="text-orange-500 dark:text-orange-400 italic">Warning</span>
                            @else
                                <span class="text-emerald-500 dark:text-emerald-400">Secure</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.document-text class="size-5 text-indigo-500" />
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Your Total Sessions</h3>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $metrics['my_total_sessions'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Recent Logs Table -->
                    <div class="md:col-span-2 rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
                        <div class="p-6 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Recent Login Activity</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-6 py-4 font-medium">Date & Time</th>
                                        <th class="px-6 py-4 font-medium">IP Address</th>
                                        <th class="px-6 py-4 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    @forelse($recent_logs as $log)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $log->ip_address }}</td>
                                            <td class="px-6 py-4">
                                                @if($log->status === 'success')
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400"><flux:icon.check-circle class="size-3" /> Success</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2 py-1 text-xs font-semibold text-red-600 dark:text-red-400"><flux:icon.x-circle class="size-3" /> Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">No login activity logged yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Personalized User Security Context -->
                    <div class="flex flex-col gap-4">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Your Security Status</h2>
                            <div class="flex flex-col gap-3">
                                <div class="flex justify-between items-center pb-3 border-b border-neutral-200 dark:border-neutral-700">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Account Lock Status</span>
                                    @if($metrics['user_is_locked'])
                                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">Locked</span>
                                    @else
                                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Secure</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center pb-3 border-b border-neutral-200 dark:border-neutral-700">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Failed Login Attempts</span>
                                    <span class="text-sm font-semibold {{ auth()->user()->failed_attempts > 0 ? 'text-orange-500 dark:text-orange-400' : 'text-zinc-900 dark:text-white' }}">
                                        {{ auth()->user()->failed_attempts }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Member Since</span>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
    </div>
</x-layouts::app>
