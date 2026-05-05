<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security Chat')] class extends Component {
    public array $messages = [];
    public string $input = '';
    public bool $loading = false;

    public function mount(): void
    {
        // Load history from DB
        $history = \App\Models\ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content'])
            ->toArray();

        if (empty($history)) {
            $this->messages[] = [
                'role' => 'system',
                'content' => 'Ask me anything about recent login events (e.g., "Show me all high-risk logins from the last 24 hours" or "Who logged in from a new device today?").',
            ];
        } else {
            $this->messages = $history;
        }
    }

    public function sendMessage(?string $question = null): void
    {
        $message = trim($question ?? $this->input);
        if ($message === '') {
            return;
        }

        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $this->input = '';
        $this->loading = true;

        try {
            $user = auth()->user();
            
            // 1. Save User Message to DB
            \App\Models\ChatMessage::create([
                'user_id' => $user->id,
                'role' => 'user',
                'content' => $message,
            ]);

            $groq = app(\App\Services\GroqRiskAssessment::class);
            
            // 2. Fetch Login Events (Role-Scoped)
            $isAdmin = $user->role === 'admin';
            $query = \App\Models\LoginLog::with('user:id,name,email');
            if (!$isAdmin) {
                $query->where('user_id', $user->id);
            }

            $events = $query->latest()->take(20)->get()
                ->map(fn (\App\Models\LoginLog $log) => [
                    'user' => $log->user?->name ?? $log->email ?? 'unknown',
                    'timestamp' => $log->created_at?->toIso8601String(),
                    'biometric_type' => $log->login_method,
                    'device' => $log->user_agent,
                    'status' => $log->status,
                    'risk_score' => $log->ai_risk_score ?? $log->risk_score,
                ]);

            $eventsJson = json_encode($events);
            
            // 3. Get AI Reply
            $reply = $groq->chat($message, $eventsJson, $user->role);

            if ($reply) {
                // 4. Save Assistant Reply to DB
                \App\Models\ChatMessage::create([
                    'user_id' => $user->id,
                    'role' => 'assistant',
                    'content' => $reply,
                ]);

                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $reply,
                ];
            } else {
                 $this->messages[] = [
                    'role' => 'assistant',
                    'content' => "I'm sorry, the AI security assistant is currently unavailable.",
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SecurityChat error: ' . $e->getMessage());
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'An error occurred while processing your request.',
            ];
        }

        $this->loading = false;
    }

    public function clearChat(): void
    {
        \App\Models\ChatMessage::where('user_id', auth()->id())->delete();
        $this->messages = [];
        $this->mount(); // Reset to system message
    }
}; ?>

<style>
    .chat-prose ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.75rem !important;
        margin-bottom: 0.75rem !important;
    }
    .chat-prose ol {
        list-style-type: decimal !important;
        padding-left: 1.5rem !important;
        margin-top: 0.75rem !important;
        margin-bottom: 0.75rem !important;
    }
    .chat-prose li {
        margin-bottom: 0.5rem !important;
        display: list-item !important;
        padding-left: 0.25rem !important;
    }
    .chat-prose p {
        margin-bottom: 1rem !important;
        line-height: 1.6 !important;
    }
    .chat-prose h1, .chat-prose h2, .chat-prose h3 {
        margin-top: 1.5rem !important;
        margin-bottom: 0.75rem !important;
        font-weight: 700 !important;
        color: #4f46e5 !important;
    }
</style>

<div class="flex flex-col gap-6 w-full max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="relative mb-2 w-full text-center lg:text-left">
        <div class="flex items-center gap-3 justify-center lg:justify-start">
            <div class="flex items-center justify-center size-10 rounded-xl bg-indigo-500 shadow-lg shadow-indigo-500/30">
                <flux:icon.chat-bubble-left-right class="size-5 text-white" />
            </div>
            <div>
                <flux:heading size="xl" level="1" class="!text-indigo-600 dark:!text-indigo-400 font-black tracking-tight">
                    {{ __('Security Chat') }}
                </flux:heading>
                <flux:subheading size="sm">
                    {{ __('AI-powered security assistant for login event analysis') }}
                </flux:subheading>
            </div>
            <flux:badge size="sm" color="indigo" class="ml-2">AI</flux:badge>
            <flux:spacer />
            @if(count($messages) > 1)
                <flux:button icon="trash" variant="ghost" size="sm" wire:click="clearChat" wire:confirm="Are you sure you want to clear your chat history?">
                    {{ __('Clear History') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Chat Container --}}
    <div class="rounded-2xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 shadow-xl shadow-zinc-900/5 flex flex-col" style="height: calc(100vh - 260px); min-height: 400px;">
        
        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-messages" 
             x-data x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
             x-effect="$wire.messages; $nextTick(() => $el.scrollTop = $el.scrollHeight)">
            
            @foreach($messages as $msg)
                @if($msg['role'] === 'system')
                    {{-- System intro message --}}
                    <div class="flex justify-center">
                        <div class="max-w-lg px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 text-center">
                            <div class="flex items-center justify-center gap-2 mb-1">
                                <flux:icon.sparkles class="size-4 text-indigo-500" />
                                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">AI Security Assistant</span>
                            </div>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $msg['content'] }}</p>
                        </div>
                    </div>
                @elseif($msg['role'] === 'user')
                    {{-- User message --}}
                    <div class="flex justify-end">
                        <div class="max-w-[75%] px-4 py-3 rounded-2xl rounded-tr-sm bg-indigo-600 text-white shadow-md">
                            <p class="text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
                        </div>
                    </div>
                @else
            {{-- AI response --}}
                    <div class="flex justify-start gap-3">
                        <div class="flex-shrink-0 flex items-start">
                            <div class="size-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                                <flux:icon.sparkles class="size-4 text-white" />
                            </div>
                        </div>
                        <div class="max-w-[85%] px-5 py-4 rounded-2xl rounded-tl-sm bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm transition-all duration-200">
                            <div class="chat-prose prose prose-sm dark:prose-invert max-w-none 
                                        text-zinc-800 dark:text-zinc-200"
                                 x-data="{ content: @js($msg['content']) }"
                                 x-html="window.marked ? window.DOMPurify.sanitize(window.marked.parse(content)) : content">
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Suggested Questions (only if chat is empty) --}}
            @if(count($messages) === 1 && !$loading)
                <div class="flex flex-col items-center gap-6 py-8">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-2">{{ __('Try asking...') }}</p>
                    
                    @php
                        $isAdmin = auth()->user()->role === 'admin';
                        $categorizedQuestions = $isAdmin ? [
                            'Threat Detection & Anomalies' => [
                                "Show me all high-risk logins in the last 24 hours",
                                "List all failed login attempts today with their IP addresses",
                                "Any logins from new or unrecognized devices this week?",
                                "Who logged in outside normal working hours (9 AM – 6 PM) recently?",
                                "Detect any brute-force patterns: multiple failures followed by success",
                                "Any impossible travel detected? (logins from distant locations in a short time)",
                            ],
                            'User & Access Audit' => [
                                "Show me the most recent activity for user [email]",
                                "Which users have the most failed login attempts this month?",
                                "List all users who have never logged in (inactive accounts)",
                                "Who has admin or privileged access and when did they last log in?",
                                "Show me logins grouped by authentication method (FaceID, Fingerprint, Google)",
                            ],
                            'Risk & Compliance Overview' => [
                                "Generate a summary of all blocked or high-risk events this week",
                                "What is the overall system risk trend over the last 7 days?",
                                "Show me a count of logins per day for the last 30 days",
                                "Export or summarize all incidents that require immediate action",
                                "Which IP addresses are flagged most often for suspicious activity?",
                            ],
                            'System Help & Guidance' => [
                                "Explain how the AI risk scoring works",
                                "What actions should I take on a blocked login attempt?",
                                "How do I register a passwordless passkey for my account?",
                                "How do I interpret the anomaly flags in the login logs?",
                                "What is the difference between 'step-up' and 'block' recommended actions?",
                            ],
                        ] : [
                            'General' => [
                                "Show my recent logins",
                                "Were any of my logins high-risk?",
                                "Explain what a risk score means",
                                "How do I register a passwordless passkey?",
                                "What devices have I used recently?",
                                "Show my latest security alerts"
                            ]
                        ];
                    @endphp

                    <div class="w-full max-w-3xl space-y-6">
                        @foreach($categorizedQuestions as $category => $questions)
                            <div class="flex flex-col gap-2">
                                @if($isAdmin)
                                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-zinc-400 dark:text-zinc-500 px-1">
                                        {{ $category }}
                                    </span>
                                @endif
                                <div class="flex flex-wrap gap-2">
                                    @foreach($questions as $question)
                                        <button 
                                            type="button"
                                            wire:click="sendMessage('{{ $question }}')"
                                            class="px-4 py-2 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 shadow-sm text-left"
                                        >
                                            {{ $question }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Loading indicator --}}
            @if($loading)
                <div class="flex justify-start gap-3">
                    <div class="flex-shrink-0 flex items-start">
                        <div class="size-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                            <flux:icon.sparkles class="size-4 text-white" />
                        </div>
                    </div>
                    <div class="px-4 py-3 rounded-2xl rounded-tl-sm bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-1.5">
                            <div class="size-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0ms;"></div>
                            <div class="size-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 150ms;"></div>
                            <div class="size-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 300ms;"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input Area --}}
        <div class="border-t border-neutral-200 dark:border-neutral-700 p-4">
            <form wire:submit="sendMessage" class="flex items-center gap-3">
                <div class="flex-1">
                    <flux:input 
                        wire:model="input" 
                        placeholder="{{ __('Ask about login events, risk patterns, anomalies...') }}"
                        icon="chat-bubble-left-ellipsis"
                        :disabled="$loading"
                        autocomplete="off"
                    />
                </div>
                <flux:button type="submit" variant="filled" size="base" :disabled="$loading" class="shadow-md shadow-indigo-500/30 whitespace-nowrap">
                    <flux:icon.paper-airplane class="size-4" />
                </flux:button>
            </form>
        </div>
    </div>
</div>
