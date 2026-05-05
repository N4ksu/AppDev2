<div class="flex flex-col gap-6" wire:poll.2s>
        
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                <flux:icon.chart-bar class="size-5" />
            </div>
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">{{ __('Live Behaviour Monitor') }}</h2>
                <p class="text-sm text-zinc-500">{{ __('Real-time tracking of your typing and cursor dynamics.') }}</p>
            </div>
        </div>

        @if(!$latestSample)
            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:icon.document-magnifying-glass class="mx-auto mb-4 size-10 text-zinc-400" />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">{{ __('No behavioral data yet.') }}</h3>
                <p class="mt-2 text-sm text-zinc-500">{{ __('Start the calibration wizard or wait 60 seconds for the background collector to send the first sample.') }}</p>
                <div class="mt-6">
                    <flux:button href="{{ route('calibrate') }}" variant="primary">{{ __('Open Calibration Wizard') }}</flux:button>
                </div>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Current Stats -->
                <div class="flex flex-col gap-6">
                    <!-- Typing Speed Card -->
                    <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.command-line class="size-5 text-emerald-500" />
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Typing Speed Now') }}</h3>
                        </div>
                        <p class="text-4xl font-black text-zinc-900 dark:text-white mb-1">
                            {{ number_format($latestSample->typing_speed, 2) }}
                            <span class="text-sm font-medium text-zinc-400 dark:text-zinc-500">cps</span>
                        </p>
                        <p class="text-xs text-zinc-500">
                            10-sample rolling average: <strong class="text-zinc-800 dark:text-zinc-300">{{ number_format($avgTyping, 2) }}</strong>
                        </p>
                        <div class="absolute -right-4 -bottom-4 opacity-5">
                            <flux:icon.command-line class="size-32" />
                        </div>
                    </div>

                    <!-- Mouse Velocity Card -->
                    <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.cursor-arrow-rays class="size-5 text-indigo-500" />
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Mouse Velocity Now') }}</h3>
                        </div>
                        <p class="text-4xl font-black text-zinc-900 dark:text-white mb-1">
                            {{ number_format($latestSample->mouse_velocity, 2) }}
                            <span class="text-sm font-medium text-zinc-400 dark:text-zinc-500">px/ms</span>
                        </p>
                        <p class="text-xs text-zinc-500">
                            10-sample rolling average: <strong class="text-zinc-800 dark:text-zinc-300">{{ number_format($avgMouse, 2) }}</strong>
                        </p>
                        <div class="absolute -right-4 -bottom-4 opacity-5">
                            <flux:icon.cursor-arrow-rays class="size-32" />
                        </div>
                    </div>
                </div>

                <!-- Live Mouse Trail Map (Local JS) -->
                <div class="flex flex-col rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Local Mouse Trail') }}</h3>
                        </div>
                        <span class="text-xs font-mono text-zinc-400 tracking-tighter">Real-time visualization</span>
                    </div>
                    
                    <div class="flex-1 min-h-[220px] rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 overflow-hidden relative" wire:ignore>
                        <canvas id="mouse-trail" class="absolute inset-0 w-full h-full"></canvas>
                        <!-- Grid overlay (purely aesthetic) -->
                        <div class="absolute inset-0 pointer-events-none opacity-20" style="background-image: radial-gradient(circle at 2px 2px, currentColor 1px, transparent 0); background-size: 20px 20px; color: #a1a1aa;"></div>
                    </div>
                </div>
            </div>
            
            <script wire:ignore>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('mouse-trail');
                if (!canvas) return;
                
                const ctx = canvas.getContext('2d');
                let positions = [];
                
                function resizeCanvas() {
                    const rect = canvas.parentElement.getBoundingClientRect();
                    canvas.width = rect.width;
                    canvas.height = rect.height;
                }
                
                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();
                
                document.addEventListener('mousemove', (e) => {
                    positions.push({x: e.clientX, y: e.clientY, time: Date.now()});
                    
                    // Remove points older than 2 seconds or if we have too many
                    const now = Date.now();
                    positions = positions.filter(p => now - p.time < 2000);
                    if (positions.length > 100) positions = positions.slice(-100);
                });
                
                function draw() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    if (positions.length > 1) {
                        ctx.beginPath();
                        for (let i = 0; i < positions.length; i++) {
                            // Scale viewport coordinates down to this local canvas
                            // Easiest approach is to just map them relative to the window size 
                            // to fit within the canvas so we see the whole screen's movements miniaturized!
                            const px = (positions[i].x / window.innerWidth) * canvas.width;
                            const py = (positions[i].y / window.innerHeight) * canvas.height;
                            
                            if (i === 0) {
                                ctx.moveTo(px, py);
                            } else {
                                ctx.lineTo(px, py);
                            }
                        }
                        
                        ctx.lineWidth = 1.5;
                        ctx.strokeStyle = 'rgba(239, 68, 68, 0.7)'; // red-500 fading
                        ctx.lineJoin = 'round';
                        ctx.lineCap = 'round';
                        ctx.stroke();
                        
                        // Draw latest dot
                        const last = positions[positions.length - 1];
                        const px = (last.x / window.innerWidth) * canvas.width;
                        const py = (last.y / window.innerHeight) * canvas.height;
                        
                        ctx.beginPath();
                        ctx.arc(px, py, 4, 0, Math.PI * 2);
                        ctx.fillStyle = '#ef4444';
                        ctx.fill();
                        
                        // Drop shadow
                        ctx.shadowColor = 'rgba(239, 68, 68, 0.5)';
                        ctx.shadowBlur = 10;
                    }
                    
                    requestAnimationFrame(draw);
                }
                
                draw();
            });
            </script>
        @endif
        
    </div>
