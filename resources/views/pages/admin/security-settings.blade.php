<?php

use App\Models\SecuritySetting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Lock Settings')] class extends Component {
    public int $max_failed_attempts;
    public int $lock_duration_minutes;

    public function mount(): void
    {
        $settings = SecuritySetting::first();
        
        $this->max_failed_attempts = $settings?->max_failed_attempts ?? 3;
        $this->lock_duration_minutes = $settings?->lock_duration_minutes ?? 15;
    }

    public function updateSettings(): void
    {
        $this->validate([
            'max_failed_attempts' => 'required|integer|min:1|max:20',
            'lock_duration_minutes' => 'required|integer|min:1|max:1440',
        ]);

        SecuritySetting::updateOrCreate(
            ['id' => 1],
            [
                'max_failed_attempts' => $this->max_failed_attempts,
                'lock_duration_minutes' => $this->lock_duration_minutes,
            ]
        );

        Flux::toast(variant: 'success', text: __('Security settings updated successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Lockdown Configuration') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Configure the system-wide thresholds for failed login detection and account locking.') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="max-w-2xl rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
        <form wire:submit="updateSettings" class="space-y-6">
            <flux:input 
                wire:model="max_failed_attempts" 
                type="number" 
                :label="__('Max Failed Attempts')" 
                :description="__('The maximum security threshold allowed before an IP or identity is automatically restricted.')"
                min="1"
                max="20"
                required
            />

            <flux:input 
                wire:model="lock_duration_minutes" 
                type="number" 
                :label="__('Lock Duration (Minutes)')" 
                :description="__('The temporal cooling-off period before the restricted account is permitted to attempt access again.')"
                min="1"
                max="1440"
                required
            />

            <div class="flex items-center gap-4">
                <flux:button variant="filled" type="submit">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>
    </div>

    <div class="relative mt-8 mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('AI Services Configuration') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Control the activation of AI-powered security features.') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="max-w-2xl rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4">
            @php
                $settings = app(\App\Services\SettingsService::class);
                $chatEnabled = $settings->get('ai_chat_enabled', '1') === '1';
            @endphp

            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="md">{{ __('AI Security Chat') }}</flux:heading>
                    <flux:subheading>{{ __('Enable or disable the AI assistant for security analysis.') }}</flux:subheading>
                </div>
                
                <form action="{{ route('admin.toggle-chat') }}" method="POST">
                    @csrf
                    <flux:button 
                        type="submit" 
                        variant="{{ $chatEnabled ? 'filled' : 'outline' }}" 
                        color="{{ $chatEnabled ? 'green' : 'zinc' }}"
                        class="shadow-sm"
                    >
                        @if($chatEnabled)
                            🟢 {{ __('AI Chat ON – Click to Turn OFF') }}
                        @else
                            🔴 {{ __('AI Chat OFF – Click to Turn ON') }}
                        @endif
                    </flux:button>
                </form>
            </div>
        </div>
    </div>
</div>
