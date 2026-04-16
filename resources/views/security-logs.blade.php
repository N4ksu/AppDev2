<x-layouts::app :title="__('Security Logs')">
    <div class="flex flex-col gap-6">
        
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Detailed Security Activity Logs</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">A complete historical audit trail of every login attempt made to the system.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <tr>
                            <th class="px-6 py-4 font-medium">Timestamp</th>
                            <th class="px-6 py-4 font-medium">Email / Account Identity</th>
                            <th class="px-6 py-4 font-medium">IP Address Source</th>
                            <th class="px-6 py-4 font-medium">Status / Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ $log->user ? $log->user->email : 'Non-existent Account' }}
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $log->ip_address }}</td>
                                <td class="px-6 py-4">
                                    @if($log->status === 'success')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            <flux:icon.check-circle class="size-3" /> LOGIN SUCCESS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                            <flux:icon.x-circle class="size-3" /> LOGIN FAILED
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 italic">
                                    No security records found in the audit database.
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
</x-layouts::app>
