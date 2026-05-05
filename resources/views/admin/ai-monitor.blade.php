<x-layouts::app title="AI Security Monitor">
    <div class="flex flex-col gap-8 w-full p-6">
        {{-- Header Section --}}
        <div class="flex items-center gap-4">
            <div class="p-4 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                <flux:icon.cpu-chip variant="solid" class="size-8" />
            </div>
            <div>
                <flux:heading size="xl" level="1" class="font-black tracking-tight">🧠 AI SECURITY MONITOR</flux:heading>
                <flux:subheading size="sm" class="font-medium">Real-time AI-powered threat detection layer</flux:subheading>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <flux:heading size="sm" class="text-zinc-400 flex items-center gap-2">
                        <flux:icon.exclamation-triangle class="size-4 text-amber-500" /> High-Risk Logins Today
                    </flux:heading>
                </div>
                <div class="text-4xl font-black text-white mb-2">{{ $highRiskCount }}</div>
                <div class="text-xs text-zinc-500">Logins scoring above 70/100 today</div>
            </div>

            <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <flux:heading size="sm" class="text-zinc-400 flex items-center gap-2">
                        <flux:icon.no-symbol class="size-4 text-red-500" /> Logins Blocked (Total)
                    </flux:heading>
                </div>
                <div class="text-4xl font-black text-white mb-2">{{ $blockedCount }}</div>
                <div class="text-xs text-zinc-500">AI-blocked logins across all time</div>
            </div>

            <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <flux:heading size="sm" class="text-zinc-400 flex items-center gap-2">
                        <flux:icon.user-group class="size-4 text-sky-500" /> Calibration Coverage
                    </flux:heading>
                </div>
                <div class="text-4xl font-black text-white mb-2">{{ $calibratedUsers }}/{{ $totalUsers }}</div>
                <div class="text-xs text-zinc-500">Users with completed calibration baseline</div>
            </div>

            <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <flux:heading size="sm" class="text-zinc-400 flex items-center gap-2">
                        <flux:icon.signal class="size-4 text-amber-500" /> Provider Degraded Today
                    </flux:heading>
                </div>
                <div class="text-4xl font-black text-white mb-2">{{ $degradedCount }}</div>
                <div class="text-xs text-zinc-500">Insights generated while provider was degraded/error</div>
            </div>
        </div>

        {{-- Main Logs Table --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 overflow-hidden shadow-xl">
            <div class="p-6 border-b border-zinc-800 flex justify-between items-center">
                <flux:heading size="md" class="flex items-center gap-2">
                    <flux:icon.clock class="size-5 text-zinc-500" /> RECENT AI-ANALYSED LOGINS
                </flux:heading>
                <div class="text-[10px] text-zinc-500 uppercase tracking-widest font-bold">Last 50 events</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-800/50 text-zinc-400">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px]">User Identity</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px]">IP Address</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px]">AI Risk Score</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px]">AI Status</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px]">Decision</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-[10px] text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @forelse($aiLogs as $log)
                            <tr class="hover:bg-zinc-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white">{{ $log->loginLog?->email ?? 'Unknown Identifier' }}</span>
                                        <span class="text-[10px] text-zinc-500 uppercase">{{ $log->loginLog?->login_method ?? 'Unknown' }} Auth</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                    {{ $log->loginLog?->ip_address }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-2 rounded-full bg-zinc-800 overflow-hidden">
                                            @php
                                                $score = (int)($log->ai_response_json['score'] ?? 0);
                                            @endphp
                                            <div class="h-full {{ $score >= 70 ? 'bg-red-500' : ($score >= 40 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ $score }}%"></div>
                                        </div>
                                        <span class="font-black {{ $score >= 70 ? 'text-red-500' : ($score >= 40 ? 'text-amber-500' : 'text-emerald-500') }}">
                                            {{ $score }}/100
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded border border-indigo-500/30 bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase mr-2">
                                        {{ $log->provider_status }}
                                    </span>
                                    <span class="text-zinc-500 text-[10px] uppercase font-medium">
                                        {{ $log->final_action === 'deny' ? 'Blocked' : 'Allowed' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] uppercase font-bold tracking-wide text-zinc-300">
                                        {{ $log->final_action }}
                                    </div>
                                    <div class="text-[10px] text-zinc-500">
                                        recommendation: {{ $log->recommendation }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-xs text-zinc-300">{{ $log->created_at->format('M d, H:i:s') }}</div>
                                    <div class="text-[10px] text-zinc-500">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-zinc-600">
                                        <flux:icon.cpu-chip class="size-12 opacity-20" />
                                        <p class="text-sm font-medium">No AI login events recorded yet.</p>
                                        <p class="text-[11px] max-w-xs opacity-60">Events appear here as users log in with AI risk assessment enabled across all authentication methods.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) return;

            window.Echo.channel('security-insights')
                .listen('.security.insight.created', () => {
                    window.location.reload();
                });
        });
    </script>
</x-layouts::app>
