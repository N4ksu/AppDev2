<x-layouts::app :title="__('Security Dashboard')">
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
                                <th class="px-6 py-4 font-medium">User</th>
                                <th class="px-6 py-4 font-medium">IP Address</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse($recent_logs as $log)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                        {{ $log->user ? $log->user->name : 'Unknown / Failed Attempt' }}
                                    </td>
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
                                    <td colspan="4" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">No login activity logged yet.</td>
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
</x-layouts::app>
