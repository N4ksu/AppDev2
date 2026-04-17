<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head', ['title' => 'Secure Login Monitoring System'])
</head>
<body class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 flex flex-col font-sans antialiased">

    <!-- 1. Navbar -->
    <nav class="sticky top-0 z-50 w-full border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2">
                    <flux:icon.shield-check class="size-6 text-indigo-500" />
                    <span class="font-bold text-lg tracking-tight">SLMS</span>
                </div>
                
                <!-- Center Links -->
                <div class="hidden md:flex space-x-8">
                    <a href="#" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">Home</a>
                    <a href="#features" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">Features</a>
                    <a href="#how-it-works" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">How it Works</a>
                    <a href="#about" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">About</a>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center gap-4">
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                            Dashboard
                        </flux:button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        
        <!-- 2. Hero Section -->
        <section class="relative pt-24 pb-32 overflow-hidden flex items-center justify-center min-h-[70vh]">
            <!-- Decorative background bloat -->
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-indigo-500 to-emerald-500 opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 mb-8 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-400 text-xs font-semibold uppercase tracking-wider">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-500"></span> Active Protection
                </div>
                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-white max-w-4xl mx-auto leading-tight mb-6">
                    Secure Login Monitoring for <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Smarter Account Protection</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    A comprehensive authentication security system designed to actively monitor login activity, detect brute-force attacks, implement automatic account locks, and provide deep forensic visibility for administrators.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary" icon="arrow-right" wire:navigate>Go to Command Center</flux:button>
                    @else
                        @if (Route::has('register'))
                            <flux:button :href="route('register')" variant="primary" wire:navigate>Get Started - Register</flux:button>
                        @endif
                        @if (Route::has('login'))
                            <flux:button :href="route('login')" variant="ghost" wire:navigate>Sign In</flux:button>
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        <!-- 3. Features Section -->
        <section id="features" class="py-24 bg-zinc-50 dark:bg-zinc-900 border-y border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-zinc-900 dark:text-white">Enterprise-Grade Security Features</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">Everything you need to monitor and secure your authentication lifecycle.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Cards -->
                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.exclamation-triangle class="size-8 text-orange-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Failed Attempt Detection</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Automatically tracks and logs failed login attempts linked to IPs and accounts, ensuring no unauthorized access goes unnoticed.</p>
                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.lock-closed class="size-8 text-red-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Temporary Account Locking</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Intervenes during brute-force attacks by temporarily disabling account access after a configured threshold of failed attempts.</p>
                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.clock class="size-8 text-emerald-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Automatic Unlock Logic</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Accounts are securely and automatically restored to normal status once the temporary locking penalty duration has expired.</p>
                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.key class="size-8 text-indigo-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Manual Admin Unlock</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Provides administrators with the tools to review locked accounts and manually restore access for legitimate users when necessary.</p>
                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.list-bullet class="size-8 text-blue-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Security Activity Logs</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Maintains a comprehensive, immutable audit trail of all successful and failed authentication events for compliance and review.</p>
                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                        <flux:icon.chart-bar class="size-8 text-purple-500 mb-4" />
                        <h3 class="text-lg font-bold mb-2">Full Audit Visibility</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">A live command center for admins to investigate incidents, view targeted accounts, filter by severity, and resolve threats.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. How It Works -->
        <section id="how-it-works" class="py-24 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white">How It Works</h2>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">A seamless, five-step flow defining secure authentication.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 relative">
                <!-- Connecting Line for larger screens -->
                <div class="hidden md:block absolute top-[45px] left-0 right-0 h-[2px] bg-zinc-200 dark:bg-zinc-800 z-0"></div>

                @php
                    $steps = [
                        ['num' => '1', 'title' => 'Submit', 'desc' => 'User submits credentials via the portal.'],
                        ['num' => '2', 'title' => 'Validate', 'desc' => 'System validates & monitors the activity.'],
                        ['num' => '3', 'title' => 'Record', 'desc' => 'Failed attempts are securely recorded.'],
                        ['num' => '4', 'title' => 'Protect', 'desc' => 'Suspicious accounts are temporarily locked.'],
                        ['num' => '5', 'title' => 'Review', 'desc' => 'Admins review logs and security events.'],
                    ];
                @endphp

                @foreach($steps as $step)
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold text-xl mb-4 shadow-lg shadow-indigo-500/20 ring-4 ring-white dark:ring-zinc-950">
                        {{ $step['num'] }}
                    </div>
                    <h4 class="font-bold mb-2 dark:text-white">{{ $step['title'] }}</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 px-2">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- 5. User Roles -->
        <section class="py-20 bg-zinc-50 dark:bg-zinc-900 border-y border-zinc-200 dark:border-zinc-800">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Standard User -->
                    <div class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">
                        <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-900 rounded-xl flex items-center justify-center mb-6">
                            <flux:icon.user class="size-6 text-zinc-700 dark:text-zinc-300" />
                        </div>
                        <h3 class="text-2xl font-bold mb-4 dark:text-white">Standard User View</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Standard users receive a streamlined, privacy-conscious dashboard. They can track their personal login success rates, active sessions, and account health independently without being overwhelmed by system-wide data.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300"><flux:icon.check-circle class="size-5 text-emerald-500" /> View personal login history</li>
                            <li class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300"><flux:icon.check-circle class="size-5 text-emerald-500" /> Clear account lock status indicators</li>
                            <li class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300"><flux:icon.check-circle class="size-5 text-emerald-500" /> Simplified, distraction-free interface</li>
                        </ul>
                    </div>

                    <!-- Administrator -->
                    <div class="relative bg-zinc-900 dark:bg-zinc-950 border border-indigo-500/30 rounded-2xl p-8 shadow-[0_0_30px_-5px_rgba(99,102,241,0.15)] overflow-hidden">
                        <!-- BG glow -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/20 blur-3xl rounded-full"></div>

                        <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center mb-6 border border-indigo-500/30 relative z-10">
                            <flux:icon.shield-exclamation class="size-6 text-indigo-400" />
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-white relative z-10">Administrator View</h3>
                        <p class="text-zinc-400 mb-6 relative z-10">
                            Administrators operate from a comprehensive Security Command Center. With real-time polling, forensic grouping, and live threat assessment, admins have total proactive control over the system's integrity.
                        </p>
                        <ul class="space-y-3 relative z-10">
                            <li class="flex items-center gap-3 text-sm text-zinc-300"><flux:icon.check-circle class="size-5 text-indigo-400" /> Live Threat & Incident Monitoring</li>
                            <li class="flex items-center gap-3 text-sm text-zinc-300"><flux:icon.check-circle class="size-5 text-indigo-400" /> Forensic Incident Resolution</li>
                            <li class="flex items-center gap-3 text-sm text-zinc-300"><flux:icon.check-circle class="size-5 text-indigo-400" /> Global Security Configuration Control</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. About Section -->
        <section id="about" class="py-24 max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-6 dark:text-white">About SLMS</h2>
            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed text-lg">
                The Secure Login Monitoring System (SLMS) is a robust, cybersecurity-focused authenticaton monitoring engine. It is designed to act as a secure gateway, providing deep auditing and proactive responses to active threats like brute-forcing, credential stuffing, and user enumeration, ensuring that your organization's digital perimeter remains uncompromised.
            </p>
        </section>

    </main>

    <!-- 7. Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 py-10 text-center bg-white dark:bg-zinc-950">
        <div class="flex items-center justify-center gap-2 mb-4">
            <flux:icon.shield-check class="size-5 text-zinc-400" />
            <span class="font-bold text-zinc-500 tracking-tight">SLMS</span>
        </div>
        <p class="text-sm text-zinc-500 dark:text-zinc-500">
            &copy; {{ date('Y') }} Secure Login Monitoring System. Built for advanced account protection.
        </p>
    </footer>

    @fluxScripts
</body>
</html>
