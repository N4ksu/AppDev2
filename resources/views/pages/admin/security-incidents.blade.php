<?php

use Livewire\Component;
use App\Models\SecurityIncident;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';
    public $severity = '';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }
    public function updatedSeverity() { $this->resetPage(); }

    public function resolveIncident($id)
    {
        $incident = SecurityIncident::findOrFail($id);
        $incident->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        $this->dispatch('toast', message: 'Incident marked as resolved.');
    }

    public function dismissIncident($id)
    {
        $incident = SecurityIncident::findOrFail($id);
        $incident->update([
            'status' => 'resolved',
            'remarks' => $incident->remarks . ' [Dismissed as false positive]',
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        $this->dispatch('toast', message: 'Incident dismissed.');
    }

    public function with()
    {
        $query = SecurityIncident::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('source_ip', 'like', '%' . $this->search . '%')
                  ->orWhere('target_identifier', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->severity) {
            $query->where('severity', $this->severity);
        }

        return [
            'incidents' => $query->latest()->paginate(10),
        ];
    }
} ?>

<div class="flex flex-col gap-6 w-full" wire:poll.5s>
    <div>
        <flux:heading size="xl" level="1">Security Incident Investigation</flux:heading>
        <flux:subheading italic>Manage and resolve automatically detected security threats</flux:subheading>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4">
        <flux:select wire:model.live="status">
            <flux:select.option value="">All Statuses</flux:select.option>
            <flux:select.option value="open">Open</flux:select.option>
            <flux:select.option value="investigating">Investigating</flux:select.option>
            <flux:select.option value="resolved">Resolved</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="severity">
            <flux:select.option value="">All Severities</flux:select.option>
            <flux:select.option value="critical">Critical</flux:select.option>
            <flux:select.option value="high">High</flux:select.option>
            <flux:select.option value="medium">Medium</flux:select.option>
            <flux:select.option value="low">Low</flux:select.option>
        </flux:select>
    </div>

    <!-- Incident Table -->
    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Type / Source</th>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Severity</th>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Target / Logs</th>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Detection Period</th>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Status</th>
                        <th class="px-6 py-4 font-medium uppercase text-[11px] tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($incidents as $incident)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-zinc-900 dark:text-white">{{ str_replace('_', ' ', ucfirst($incident->type)) }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono">{{ $incident->source_ip }}</div>
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
                            <td class="px-6 py-4">
                                <div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $incident->target_identifier ?? 'Multiple Accounts' }}</div>
                                <div class="text-[11px] text-zinc-500">Related events: {{ $incident->logs_count }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-zinc-900 dark:text-white">Started: {{ ($incident->first_detected_at ?? $incident->created_at)?->format('M d, H:i') }}</div>
                                <div class="text-[11px] text-zinc-500 italic">Last Activity: {{ ($incident->last_detected_at ?? $incident->created_at)?->diffForHumans() }}</div>
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
                                <div class="flex items-center gap-1.5 {{ $incident->status === 'open' ? 'text-orange-600' : ($incident->status === 'resolved' ? 'text-emerald-600' : 'text-zinc-500') }}">
                                    <flux:icon :icon="$statusIcon" class="size-4" />
                                    <span class="font-semibold">{{ ucfirst($incident->status) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($incident->status !== 'resolved')
                                    <div class="flex gap-2">
                                        <flux:button size="xs" variant="primary" wire:click="resolveIncident({{ $incident->id }})" wire:confirm="Mark this incident as resolved?">Resolve</flux:button>
                                        <flux:button size="xs" variant="ghost" wire:click="dismissIncident({{ $incident->id }})" wire:confirm="Dismiss this incident as a false positive?">Dismiss</flux:button>
                                    </div>
                                @else
                                    <div class="text-xs text-zinc-400 italic">Resolved {{ $incident->resolved_at?->diffForHumans() }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                <flux:icon.shield-check class="mx-auto size-12 mb-4 opacity-20" />
                                No incidents matching your filters were found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidents->hasPages())
            <div class="p-6 border-t border-neutral-200 dark:border-neutral-700">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
</div>
