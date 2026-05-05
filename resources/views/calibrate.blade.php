<x-layouts::app :title="__('Behavioural Calibration Wizard')">
    <div class="mx-auto max-w-3xl flex flex-col gap-6">
        
        <div class="rounded-xl border border-neutral-200 bg-white p-8 dark:border-neutral-700 dark:bg-zinc-900 shadow-sm flex flex-col">
            <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase italic tracking-tight mb-2 flex items-center gap-2">
                <flux:icon.cpu-chip class="size-6 text-indigo-500" />
                {{ __('Behavioural Calibration Wizard') }}
            </h2>
            <p class="text-zinc-500 mb-8">{{ __('Train your personal AI security model by completing these two quick exercises.') }}</p>

            <div id="calibration-status-box" class="mb-6 hidden rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm dark:border-sky-900 dark:bg-sky-950/20">
                <p id="calibration-status-text" class="font-medium text-zinc-700 dark:text-zinc-300"></p>
                <p id="calibration-status-meta" class="mt-1 text-xs text-zinc-500"></p>
                <div class="mt-3 flex gap-2">
                    <button id="keep-profile-btn" type="button" class="hidden rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Keep Current Profile
                    </button>
                    <button id="recalibrate-btn" type="button" class="hidden rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                        Recalibrate Now
                    </button>
                </div>
            </div>

            <button id="start-wizard" class="w-full sm:w-auto inline-flex justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20">
                🎓 Start Calibration Process
            </button>
            
            <!-- Wizard Container -->
            <div id="calibration-wizard" class="mt-8 hidden rounded-xl border border-indigo-100 bg-indigo-50/30 p-6 dark:border-indigo-900/30 dark:bg-indigo-900/10">
               <!-- Progress bar -->
               <div class="mb-6 h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                  <div id="wizard-progress-bar" class="h-2 rounded-full bg-indigo-500 transition-all duration-300" style="width: 0%;"></div>
               </div>
               <p id="wizard-step-title" class="text-base font-bold uppercase tracking-tight text-indigo-600 dark:text-indigo-400"></p>
               <p id="wizard-instructions" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400"></p>
               
               <!-- Typing test area -->
               <div id="step-typing" class="mt-6 hidden animate-fade-in">
                  <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700 shadow-sm mb-4">
                      <p class="whitespace-pre-wrap text-xl font-mono font-medium tracking-tight text-zinc-800 dark:text-zinc-200 select-none" id="typing-sentence"></p>
                  </div>
                  <textarea id="typing-input" rows="3" class="w-full rounded-xl border border-zinc-300 p-4 font-mono shadow-inner focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white transition-all text-lg" placeholder="Start typing the sentence above to begin..."></textarea>
                  <p id="typing-timer" class="mt-3 text-sm font-bold text-zinc-500 flex items-center gap-2"><flux:icon.clock class="size-4" /> <span>Time elapsed: 0s</span></p>
               </div>

               <!-- Mouse tracking area -->
               <div id="step-mouse" class="mt-6 hidden animate-fade-in">
                  <div class="relative rounded-xl overflow-hidden shadow-inner border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                      <canvas id="tracking-canvas" width="600" height="350" class="w-full max-w-full cursor-crosshair block"></canvas>
                  </div>
                  <p id="mouse-status" class="mt-3 text-sm font-bold text-zinc-500 flex items-center gap-2"><flux:icon.cursor-arrow-rays class="size-4" /> <span>Follow the red dot with your cursor!</span></p>
               </div>
               
               <div id="wizard-complete" class="mt-6 hidden text-center p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-base font-bold text-emerald-700 dark:text-emerald-400"></div>
            </div>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sentences = [
            "The quick brown fox jumps over the lazy dog.",
            "Pack my box with five dozen liquor jugs.",
            "How vexingly quick daft zebras jump!",
            "Sphinx of black quartz, judge my vow.",
            "Two driven jocks help fax my big quiz.",
            "The five boxing wizards jump quickly."
        ];
        
        let calibrating = false;
        let calibrationSamples = [];
        let typingInterval = null;
        let mouseInterval = null;
        let animationFrameId = null;
        
        const btn = document.getElementById('start-wizard');
        const wizardEl = document.getElementById('calibration-wizard');
        const progressEl = document.getElementById('wizard-progress-bar');
        const titleEl = document.getElementById('wizard-step-title');
        const instructionsEl = document.getElementById('wizard-instructions');
        
        const stepTypingEl = document.getElementById('step-typing');
        const typingSentenceEl = document.getElementById('typing-sentence');
        const typingInputEl = document.getElementById('typing-input');
        const typingTimerEl = document.getElementById('typing-timer');
        const typingTimerSpan = typingTimerEl.querySelector('span');
        
        const stepMouseEl = document.getElementById('step-mouse');
        const canvas = document.getElementById('tracking-canvas');
        const mouseStatusEl = document.getElementById('mouse-status');
        const mouseStatusSpan = mouseStatusEl.querySelector('span');
        
        const completeEl = document.getElementById('wizard-complete');
        const statusBox = document.getElementById('calibration-status-box');
        const statusText = document.getElementById('calibration-status-text');
        const statusMeta = document.getElementById('calibration-status-meta');
        const keepProfileBtn = document.getElementById('keep-profile-btn');
        const recalibrateBtn = document.getElementById('recalibrate-btn');

        if (!btn) return;

        fetch('{{ route('behavior.status') }}')
            .then((res) => res.json())
            .then((payload) => {
                const data = payload?.data || {};
                const state = data.calibration_state;
                if (!state) return;

                statusBox.classList.remove('hidden');
                statusMeta.textContent = `Last calibrated: ${data.calibrated_at ?? 'Never'} | Samples: ${data.sample_count ?? 0}`;

                if (state === 'calibrated') {
                    statusText.textContent = 'You are already calibrated. You can keep your current profile or recalibrate if your behavior changed.';
                    keepProfileBtn.classList.remove('hidden');
                    recalibrateBtn.classList.remove('hidden');
                    btn.classList.add('hidden');
                } else if (state === 'recalibration_recommended') {
                    statusText.textContent = 'Recalibration is recommended to keep behavior verification accurate.';
                    recalibrateBtn.classList.remove('hidden');
                    btn.classList.remove('hidden');
                } else if (state === 'verification_degraded') {
                    statusText.textContent = 'Verification is currently running in local-protection mode. Calibration remains available.';
                    btn.classList.remove('hidden');
                } else {
                    statusText.textContent = 'No calibration profile found yet. Start calibration to train your baseline.';
                    btn.classList.remove('hidden');
                }
            })
            .catch(() => {
                statusBox.classList.remove('hidden');
                statusText.textContent = 'Unable to load calibration status right now.';
                btn.classList.remove('hidden');
            });

        keepProfileBtn?.addEventListener('click', () => {
            window.location.href = '{{ route('dashboard') }}';
        });

        recalibrateBtn?.addEventListener('click', () => {
            btn.classList.remove('hidden');
            btn.click();
        });

        btn.addEventListener('click', () => {
            if (calibrating) return;
            calibrating = true;
            calibrationSamples = [];
            
            btn.style.display = 'none';
            wizardEl.classList.remove('hidden');
             
            // Warn if leaving
            window.addEventListener('beforeunload', beforeUnloadHandler);
            
            startTypingTest();
        });
        
        function beforeUnloadHandler(e) {
            if (calibrating) {
                e.preventDefault();
                e.returnValue = '';
            }
        }
        
        function startTypingTest() {
            titleEl.textContent = "Step 1: Typing Speed Test";
            instructionsEl.textContent = "Type the sentence exactly as shown below. We will monitor your typing dynamics.";
            progressEl.style.width = '25%';
            stepTypingEl.classList.remove('hidden');
            
            const targetText = sentences[Math.floor(Math.random() * sentences.length)];
            typingSentenceEl.textContent = targetText;
            typingInputEl.value = '';
            typingInputEl.disabled = false;
            typingInputEl.focus();
            
            let lastCharCount = 0;
            let timePassed = 0;
            let noInputTime = 0;
            let hasStarted = false; // start timer only after first keystroke
            
            typingInputEl.addEventListener('input', function startTimer() {
                if (hasStarted) return;
                hasStarted = true;
                typingInputEl.removeEventListener('input', startTimer);
                typingTimerSpan.textContent = `Time elapsed: 0s`;
                
                typingInterval = setInterval(() => {
                    const currentText = typingInputEl.value;
                    const charsTyped = currentText.length - lastCharCount;
                    
                    // Speed is chars typed in this second
                    let typingSpeed = charsTyped > 0 ? charsTyped : 0;
                    calibrationSamples.push({typing_speed: typingSpeed, mouse_velocity: 0});
                    
                    if (charsTyped === 0) {
                        noInputTime++;
                    } else {
                        noInputTime = 0;
                    }
                    
                    lastCharCount = currentText.length;
                    timePassed++;
                    
                    typingTimerSpan.textContent = `Time elapsed: ${timePassed}s`;
                    
                    // Check if completed or timeout (max 30s) or idle for 5s
                    if (currentText.trim() === targetText || (timePassed > 5 && noInputTime >= 5) || timePassed >= 30) {
                        clearInterval(typingInterval);
                        typingInputEl.disabled = true;
                        
                        const wpm = Math.round((currentText.length / 5) / (timePassed / 60)) || 0;
                        typingTimerEl.className = 'mt-3 text-sm font-bold text-emerald-600 flex items-center gap-2';
                        typingTimerSpan.textContent = `Finished! Your speed: ~${wpm} WPM. Proceeding to Step 2...`;
                        
                        setTimeout(() => {
                            stepTypingEl.classList.add('hidden');
                            startMouseTest();
                        }, 2000);
                    }
                }, 1000);
            }, { once: true });
        }
        
        function startMouseTest() {
            titleEl.textContent = "Step 2: Mouse Tracking Test";
            instructionsEl.textContent = "Follow the moving red dot with your cursor as closely as possible for 10 seconds.";
            progressEl.style.width = '75%';
            stepMouseEl.classList.remove('hidden');
            
            const ctx = canvas.getContext('2d');
            
            // Sync canvas dimensions
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            
            let dotX = canvas.width / 2;
            let dotY = canvas.height / 2;
            let targetX = dotX;
            let targetY = dotY;
            
            let hasStarted = false;
            let secondsLeft = 10;
            let mousePositions = [];
            
            // Function to run the 10 sec logic
            function beginTracking() {
                hasStarted = true;
                
                // Move target dot every 1.5 seconds
                const moveDotInterval = setInterval(() => {
                    targetX = Math.random() * (canvas.width - 40) + 20;
                    targetY = Math.random() * (canvas.height - 40) + 20;
                }, 1500);
                
                mouseInterval = setInterval(() => {
                    let mouseVelocity = 0;
                    if (mousePositions.length >= 2) {
                        let totalDist = 0;
                        let totalTime = 0;
                        for (let i = 1; i < mousePositions.length; i++) {
                            let dx = mousePositions[i].x - mousePositions[i-1].x;
                            let dy = mousePositions[i].y - mousePositions[i-1].y;
                            totalDist += Math.sqrt(dx*dx + dy*dy);
                            totalTime += mousePositions[i].time - mousePositions[i-1].time;
                        }
                        mouseVelocity = totalTime > 0 ? (totalDist / totalTime) : 0;
                    }
                    
                    calibrationSamples.push({typing_speed: 0, mouse_velocity: mouseVelocity});
                    mousePositions = [];
                    
                    secondsLeft--;
                    mouseStatusSpan.textContent = `Time remaining: ${secondsLeft}s`;
                    
                    if (secondsLeft <= 0) {
                        clearInterval(mouseInterval);
                        clearInterval(moveDotInterval);
                        cancelAnimationFrame(animationFrameId);
                        canvas.removeEventListener('mousemove', onMouseMove);
                        
                        mouseStatusEl.className = 'mt-3 text-sm font-bold text-emerald-600 flex items-center gap-2';
                        mouseStatusSpan.textContent = `Completed tracking! Synchronizing metrics...`;
                        
                        setTimeout(finishCalibration, 1000);
                    }
                }, 1000);
            }
            
            function animate() {
                // Smoothly interpolate dot towards target
                dotX += (targetX - dotX) * 0.05;
                dotY += (targetY - dotY) * 0.05;
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.beginPath();
                ctx.arc(dotX, dotY, 12, 0, Math.PI * 2);
                ctx.fillStyle = '#ef4444';
                ctx.fill();
                
                ctx.shadowColor = 'rgba(239, 68, 68, 0.6)';
                ctx.shadowBlur = 15;
                ctx.closePath();
                
                animationFrameId = requestAnimationFrame(animate);
            }
            animate();
            
            const onMouseMove = (e) => {
                if (!hasStarted) beginTracking();
                const rect = canvas.getBoundingClientRect();
                mousePositions.push({
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top,
                    time: Date.now()
                });
            };
            canvas.addEventListener('mousemove', onMouseMove);
        }
        
        async function finishCalibration() {
            titleEl.textContent = "Processing...";
            instructionsEl.textContent = "Synthesizing samples & training AI behavioral model...";
            progressEl.style.width = '100%';
            stepMouseEl.classList.add('hidden');
            completeEl.textContent = "Training in progress. This typically takes 3-5 seconds...";
            completeEl.classList.remove('hidden');
            
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            
            try {
                const res = await fetch('/behavior/calibrate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ samples: calibrationSamples })
                });
                const data = await res.json();
                if (data.code === 'CALIBRATION_COMPLETED') {
                    const samples = data.data?.samples_recorded ?? 0;
                    completeEl.innerHTML = `✓ Calibration successful!<br><span class="text-xs font-normal opacity-80">${samples} data points analyzed. Activating secure mode...</span>`;
                    setTimeout(() => window.location.href = '/dashboard', 2000);
                } else {
                    completeEl.className = "mt-6 text-center p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-base font-bold text-red-700 dark:text-red-400";
                    completeEl.textContent = 'Calibration failed. Please try again.';
                    calibrating = false;
                    btn.style.display = 'inline-flex';
                }
            } catch (err) {
                completeEl.className = "mt-6 text-center p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-base font-bold text-red-700 dark:text-red-400";
                completeEl.textContent = 'Network error during calibration. Please try again.';
                calibrating = false;
                btn.style.display = 'inline-flex';
            }
        }
    });
    </script>
</x-layouts::app>
