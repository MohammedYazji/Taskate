<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Taskate - Organize Your Tasks Beautifully</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Figtree', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#F0FDFA',100:'#CCFBF1',200:'#99F6E4',300:'#5EEAD4',400:'#2DD4BF',500:'#14B8A6',600:'#0D9488',700:'#0F766E',800:'#115E59',900:'#134E4A' },
                    },
                },
            },
        }
    </script>
    <style>
        * { font-family: 'Figtree', system-ui, sans-serif; }

        .glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
        }
        .glow-blue { background: #99F6E4; animation: float 10s ease-in-out infinite; }
        .glow-teal { background: #CCFBF1; animation: float 12s ease-in-out infinite reverse; }
        .glow-amber { background: #FEF3C7; animation: float 11s ease-in-out infinite 2s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-40px) scale(1.08); }
        }

        .fade-up { opacity: 0; transform: translateY(40px); transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1); }
        .fade-up.visible { opacity: 1; transform: translateY(0); }
        .fade-up-d1 { transition-delay: 0.1s; }
        .fade-up-d2 { transition-delay: 0.2s; }
        .fade-up-d3 { transition-delay: 0.3s; }

        .feature-card { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 24px 48px rgba(20, 184, 166, 0.1); }

        .mockup-shadow {
            box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.02);
        }

        .nav-blur { backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); }

        .gradient-text {
            background: linear-gradient(135deg, #14B8A6 0%, #0D9488 50%, #0F766E 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .cta-primary {
            background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%);
            transition: all 0.3s ease;
        }
        .cta-primary:hover {
            background: linear-gradient(135deg, #0D9488 0%, #0F766E 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(20, 184, 166, 0.3);
        }

        .cta-secondary {
            transition: all 0.3s ease;
        }
        .cta-secondary:hover {
            background: #F0FDFA;
            border-color: #14B8A6;
            color: #0D9488;
        }

        .google-btn { transition: all 0.2s ease; }
        .google-btn:hover { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08); transform: translateY(-1px); }

        .tab-active { background: #F0FDFA; color: #0D9488; border-color: #14B8A6; }
        .tab-item { transition: all 0.2s ease; cursor: pointer; }
        .tab-item:hover { background: #F9FAFB; }

        .highlight-box { position: relative; }
        .highlight-box::before {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            right: 0;
            height: 12px;
            background: linear-gradient(90deg, #99F6E4 0%, #CCFBF1 100%);
            border-radius: 4px;
            z-index: -1;
        }

        .dark-section {
            background: linear-gradient(135deg, #115E59 0%, #134E4A 50%, #0F766E 100%);
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased overflow-x-hidden">

    <!-- Nav -->
    <nav class="fixed top-0 left-0 right-0 z-50 nav-blur bg-white/80 border-b border-gray-100/50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <img src="{{ asset('logo.svg') }}" alt="Taskate" class="w-8 h-8">
                <span class="text-xl font-bold text-gray-900">Taskate</span>
            </a>
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="cta-primary inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-semibold text-white shadow-sm">
                            Get started
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-900 transition px-3 py-2">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition px-3 py-2">Sign in</a>
                        <a href="{{ route('google.redirect') }}" class="google-btn inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:border-gray-300 shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                            Sign up with Google
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- ==================== HERO ==================== -->
    <section class="relative min-h-screen flex items-center pt-16 overflow-hidden">
        <div class="glow glow-blue w-[500px] h-[500px] -top-40 -left-40" style="animation-delay:0s;"></div>
        <div class="glow glow-teal w-[400px] h-[400px] bottom-0 right-10" style="animation-delay:4s;"></div>

        <div class="max-w-7xl mx-auto px-6 py-20 relative z-10 w-full">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-5xl md:text-7xl font-extrabold leading-[1.08] tracking-tight fade-up">
                    Stay Organized,<br>
                    <span class="gradient-text">Stay Creative.</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-gray-500 leading-relaxed max-w-xl mx-auto fade-up fade-up-d1">
                    Join thousands of people to capture ideas, organize life, and do something creative with Taskate.
                </p>
                <div class="mt-9 flex flex-wrap items-center justify-center gap-4 fade-up fade-up-d2">
                    <a href="{{ route('google.redirect') }}" class="google-btn inline-flex items-center gap-3 px-7 py-3.5 bg-white border border-gray-200 rounded-full text-sm font-semibold text-gray-700 hover:border-gray-300 shadow-sm">
                        <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        Get Started
                    </a>
                    <a href="#features" class="cta-secondary inline-flex items-center gap-2 px-7 py-3.5 border border-gray-200 rounded-full text-sm font-semibold text-gray-700">
                        Learn More
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                </div>
                <div class="mt-8 flex items-center justify-center gap-5 text-sm text-gray-400 fade-up fade-up-d3">
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Free</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>No credit card</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Sign up with Google</span>
                </div>
            </div>

            <!-- Hero App Mockup -->
            <div class="mt-16 max-w-5xl mx-auto fade-up fade-up-d3">
                <div class="mockup-shadow rounded-2xl bg-white border border-gray-100 overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <div class="flex gap-1.5"><div class="w-3 h-3 rounded-full bg-red-300"></div><div class="w-3 h-3 rounded-full bg-yellow-300"></div><div class="w-3 h-3 rounded-full bg-green-300"></div></div>
                        <div class="flex-1 mx-8"><div class="bg-white rounded-md border border-gray-200 h-6 w-48 mx-auto"></div></div>
                    </div>
                    <div class="flex h-80">
                        <div class="w-44 bg-gray-50 border-r border-gray-100 p-3 space-y-1.5 hidden md:block">
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-brand-50 text-brand-700 text-xs font-medium"><span class="w-3.5 h-3.5 rounded bg-brand-200"></span>Dashboard</div>
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-gray-500 text-xs"><span class="w-3.5 h-3.5 rounded bg-gray-200"></span>Projects</div>
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-gray-500 text-xs"><span class="w-3.5 h-3.5 rounded bg-gray-200"></span>Calendar</div>
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-gray-500 text-xs"><span class="w-3.5 h-3.5 rounded bg-gray-200"></span>Focus</div>
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-2 mb-1">Lists</div>
                                <div class="flex items-center gap-2 px-2 py-1 text-xs text-gray-600"><span class="w-2 h-2 rounded-full bg-brand-400"></span>Work</div>
                                <div class="flex items-center gap-2 px-2 py-1 text-xs text-gray-600"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Personal</div>
                                <div class="flex items-center gap-2 px-2 py-1 text-xs text-gray-600"><span class="w-2 h-2 rounded-full bg-blue-400"></span>Design</div>
                            </div>
                        </div>
                        <div class="flex-1 p-5 space-y-2.5">
                            <div class="text-sm font-semibold text-gray-800">Today's Tasks</div>
                            @foreach(['Review design mockups' => 'high', 'Update project timeline' => 'medium', 'Team standup meeting' => '', 'Write documentation' => '', 'Fix navigation bug' => 'high'] as $task => $pri)
                            <div class="flex items-center gap-3 p-2.5 rounded-lg {{ $loop->index < 2 ? 'bg-brand-50/50 border border-brand-100' : 'bg-gray-50 border border-gray-100' }}">
                                <div class="w-4 h-4 rounded border-2 {{ $loop->index < 2 ? 'border-brand-400 bg-brand-50' : 'border-gray-300' }} flex-shrink-0"></div>
                                <div class="flex-1 text-xs font-medium {{ $loop->index < 2 ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $task }}</div>
                                @if($pri === 'high')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-red-50 text-red-600">High</span>
                                @elseif($pri === 'medium')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-amber-50 text-amber-600">Med</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FEATURE SHOWCASE (TickTick style alternating) ==================== -->
    <section id="features" class="py-28 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-20 fade-up">
                <span class="inline-block px-3 py-1 rounded-full bg-brand-50 text-brand-600 text-xs font-semibold tracking-wide mb-4">FEATURES</span>
                <h2 class="text-4xl md:text-5xl font-bold tracking-tight">
                    <span class="highlight-box">Powerful</span> and intuitive features
                </h2>
                <p class="mt-4 text-gray-500 max-w-lg mx-auto text-lg">Simplify your daily planning</p>
            </div>

            <!-- Feature 1: Task Management -->
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-24 fade-up">
                <div>
                    <div class="text-brand-500 font-semibold text-sm mb-3">To-Do List</div>
                    <h3 class="text-3xl font-bold mb-4">Organize everything in your life</h3>
                    <p class="text-gray-500 leading-relaxed mb-6">Whether it's work projects, personal tasks, or study plans, Taskate helps you organize and confidently tackle everything in your life.</p>
                    <ul class="space-y-3">
                        @foreach(['Create tasks with due dates, priorities, and tags' => '', 'Drag and drop to reorder and reorganize' => '', 'Group tasks into lists, folders, and sections' => ''] as $item)
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mockup-shadow rounded-2xl bg-white border border-gray-100 p-6">
                    <div class="space-y-2">
                        @foreach(['Research competitors' => 'brand', 'Design new dashboard' => 'blue', 'Fix login bug' => 'red', 'Write unit tests' => 'brand', 'Deploy to production' => 'amber'] as $i => $task)
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $i < 2 ? 'bg-brand-50 border border-brand-100' : 'bg-gray-50 border border-gray-100' }}">
                            <div class="w-5 h-5 rounded-md border-2 {{ $i < 2 ? 'border-brand-400 bg-brand-50' : 'border-gray-300' }} flex-shrink-0 flex items-center justify-center">
                                @if($i < 2)<svg class="w-3 h-3 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>@endif
                            </div>
                            <div class="flex-1 text-sm {{ $i < 2 ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $task }}</div>
                            @if($i == 2)<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-600">Urgent</span>@endif
                            @if($i == 4)<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-600">Medium</span>@endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Feature 2: Calendar -->
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-24 fade-up">
                <div class="order-2 lg:order-1 mockup-shadow rounded-2xl bg-white border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 px-4 py-2 text-xs font-semibold text-gray-500">July 2026</div>
                    <div class="grid grid-cols-7 text-center text-[10px] text-gray-400 py-2 border-b border-gray-100">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)<div>{{ $d }}</div>@endforeach
                    </div>
                    <div class="grid grid-cols-7 text-center text-xs p-3 gap-1">
                        @foreach(range(1, 31) as $d)
                            <div class="aspect-square rounded-lg flex items-center justify-center {{ $d == 15 ? 'bg-brand-500 text-white font-bold' : ($d == 8 || $d == 22 ? 'bg-red-50 text-red-600 font-medium' : ($d == 3 || $d == 10 || $d == 18 ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50')) }} cursor-pointer transition">
                                {{ $d }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="text-blue-500 font-semibold text-sm mb-3">Calendar Views</div>
                    <h3 class="text-3xl font-bold mb-4">Easily plan your schedule</h3>
                    <p class="text-gray-500 leading-relaxed mb-6">Different calendar views like monthly, weekly, and daily help you plan your time more efficiently. Never miss a deadline.</p>
                    <ul class="space-y-3">
                        @foreach(['Monthly View provides a clear overview' => '', 'Weekly View highlights busy and free time' => '', 'Drag tasks to reschedule instantly' => ''] as $item)
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Feature 3: Pomodoro -->
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-24 fade-up">
                <div>
                    <div class="text-orange-500 font-semibold text-sm mb-3">Pomodoro</div>
                    <h3 class="text-3xl font-bold mb-4">Track time and stay focused</h3>
                    <p class="text-gray-500 leading-relaxed mb-6">Adopt the popular Pomodoro Technique — break tasks into 25-minute intervals to stay focused and achieve a productive flow.</p>
                    <ul class="space-y-3">
                        @foreach(['Built-in 25-minute focus timer' => '', 'Track completed focus sessions' => '', 'Set custom durations for deep work' => ''] as $item)
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mockup-shadow rounded-2xl bg-white border border-gray-100 p-8 flex flex-col items-center">
                    <div class="w-40 h-40 rounded-full border-[6px] border-brand-400 flex items-center justify-center mb-4 relative">
                        <div class="absolute inset-0 rounded-full border-[6px] border-gray-100"></div>
                        <div class="text-3xl font-bold text-brand-600">18:42</div>
                    </div>
                    <div class="text-sm text-gray-500 mb-4">Session 2 of 4</div>
                    <div class="flex gap-2">
                        <div class="w-10 h-10 rounded-full bg-brand-500 text-white flex items-center justify-center cursor-pointer hover:bg-brand-600 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center cursor-pointer hover:bg-gray-200 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature 4: Eisenhower Matrix -->
            <div class="grid lg:grid-cols-2 gap-16 items-center fade-up">
                <div class="order-2 lg:order-1 mockup-shadow rounded-2xl bg-white border border-gray-100 p-6">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                            <div class="text-[10px] font-bold text-red-600 uppercase tracking-wider mb-2">Do First</div>
                            <div class="text-xs text-red-700 bg-white rounded-lg p-2 border border-red-100 mb-1.5">Fix critical bug</div>
                            <div class="text-xs text-red-700 bg-white rounded-lg p-2 border border-red-100">Client deadline</div>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-100">
                            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-2">Schedule</div>
                            <div class="text-xs text-amber-700 bg-white rounded-lg p-2 border border-amber-100 mb-1.5">Plan roadmap</div>
                            <div class="text-xs text-amber-700 bg-white rounded-lg p-2 border border-amber-100">Team meeting</div>
                        </div>
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                            <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Delegate</div>
                            <div class="text-xs text-blue-700 bg-white rounded-lg p-2 border border-blue-100 mb-1.5">Code review</div>
                            <div class="text-xs text-blue-700 bg-white rounded-lg p-2 border border-blue-100">Update docs</div>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Delete</div>
                            <div class="text-xs text-gray-500 bg-white rounded-lg p-2 border border-gray-200 mb-1.5 line-through">Old reports</div>
                            <div class="text-xs text-gray-500 bg-white rounded-lg p-2 border border-gray-200 line-through">Unused apps</div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="text-purple-500 font-semibold text-sm mb-3">Eisenhower Matrix</div>
                    <h3 class="text-3xl font-bold mb-4">Prioritize what matters most</h3>
                    <p class="text-gray-500 leading-relaxed mb-6">The Eisenhower Matrix helps you decide what to do, schedule, delegate, or drop. Separate urgent from important to work smarter.</p>
                    <ul class="space-y-3">
                        @foreach(['4-quadrant priority system' => '', 'Drag tasks between quadrants' => '', 'Auto-urgency from due dates' => ''] as $item)
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-purple-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== GALLERY (comprehensive features) ==================== -->
    <section class="py-28 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16 fade-up">
                <h2 class="text-4xl font-bold tracking-tight">
                    A <span class="highlight-box">comprehensive</span> suite of features
                </h2>
                <p class="mt-4 text-gray-500">Meet your unique needs</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 fade-up">
                @php
                $gallery = [
                    ['title' => 'Smart Lists', 'desc' => 'Organize tasks with custom lists, folders, and sections.', 'color' => 'brand', 'icon' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
                    ['title' => 'Calendar View', 'desc' => 'Monthly, weekly, and daily views for visual planning.', 'color' => 'blue', 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                    ['title' => 'Focus Timer', 'desc' => 'Pomodoro timer with session tracking for deep work.', 'color' => 'orange', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                    ['title' => 'Priority Matrix', 'desc' => 'Eisenhower matrix to separate urgent from important.', 'color' => 'purple', 'icon' => '<path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
                    ['title' => 'Kanban Board', 'desc' => 'Visual board view to track tasks across stages.', 'color' => 'pink', 'icon' => '<path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>'],
                    ['title' => 'Drag & Drop', 'desc' => 'Reorder tasks and move between lists effortlessly.', 'color' => 'teal', 'icon' => '<path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>'],
                    ['title' => 'Tags', 'desc' => 'Color-coded tags for quick filtering and organization.', 'color' => 'red', 'icon' => '<path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                    ['title' => 'Sections', 'desc' => 'Break projects into sections for better structure.', 'color' => 'indigo', 'icon' => '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>'],
                    ['title' => 'Quick Add', 'desc' => 'Instantly add tasks from anywhere in the app.', 'color' => 'green', 'icon' => '<path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'],
                ];
                @endphp

                @foreach($gallery as $item)
                <div class="feature-card p-6 rounded-2xl bg-white border border-gray-100">
                    <div class="w-11 h-11 rounded-xl bg-{{ $item['color'] }}-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-{{ $item['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">{!! $item['icon'] !!}</svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-1.5">{{ $item['title'] }}</h4>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ==================== SYNC SECTION (dark bg) ==================== -->
    <section class="dark-section py-28 relative overflow-hidden">
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-brand-400 rounded-full filter blur-[120px] opacity-20"></div>
        <div class="absolute bottom-0 right-1/4 w-48 h-48 bg-brand-300 rounded-full filter blur-[100px] opacity-15"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white leading-tight">Sync across<br>all platforms</h2>
                    <p class="mt-5 text-brand-200 text-lg leading-relaxed max-w-md">Whether it's your phone, computer, or tablet, Taskate offers real-time sync and a seamless experience everywhere.</p>
                    <div class="mt-8 flex gap-4">
                        <a href="{{ route('google.redirect') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 rounded-full text-sm font-semibold hover:bg-brand-50 transition shadow-lg">
                            Get Started Free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </div>
                <div class="relative hidden lg:flex items-center justify-center gap-4">
                    <!-- Desktop mockup -->
                    <div class="w-64 h-44 rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm p-3 transform -rotate-2">
                        <div class="flex gap-1 mb-2"><div class="w-2 h-2 rounded-full bg-red-400"></div><div class="w-2 h-2 rounded-full bg-yellow-400"></div><div class="w-2 h-2 rounded-full bg-green-400"></div></div>
                        <div class="space-y-1.5">
                            @foreach(['Design review' => '', 'Ship feature' => '', 'Update docs' => ''] as $t)
                            <div class="flex items-center gap-2 text-[10px] text-white/80"><div class="w-3 h-3 rounded border border-white/30"></div>{{ $t }}</div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Phone mockup -->
                    <div class="w-28 h-52 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-sm p-2 transform rotate-2">
                        <div class="text-[8px] text-white/60 font-semibold mb-2">Today</div>
                        <div class="space-y-1">
                            @foreach(['Review PR' => '', 'Standup' => '', 'Ship v2' => '', 'Write tests' => ''] as $t)
                            <div class="flex items-center gap-1 text-[8px] text-white/70"><div class="w-2 h-2 rounded border border-white/30"></div>{{ $t }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CTA ==================== -->
    <section class="py-28 bg-white relative overflow-hidden">
        <div class="glow glow-blue w-[400px] h-[400px] -top-20 left-1/4" style="animation-delay:1s;"></div>
        <div class="glow glow-amber w-[300px] h-[300px] bottom-0 right-1/3" style="animation-delay:3s;"></div>

        <div class="max-w-3xl mx-auto px-6 text-center relative z-10 fade-up">
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                Ready to be more <span class="gradient-text">productive</span>?
            </h2>
            <p class="mt-5 text-lg text-gray-500 max-w-lg mx-auto">
                Join Taskate and experience task management that actually feels good. Sign up in seconds with your Google account.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('google.redirect') }}" class="google-btn inline-flex items-center gap-3 px-8 py-4 bg-white border border-gray-200 rounded-full text-base font-semibold text-gray-700 hover:border-gray-300 shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    Get started with Google
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer class="border-t border-gray-100 py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-8 mb-10">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <img src="{{ asset('logo.svg') }}" alt="Taskate" class="w-7 h-7">
                        <span class="text-lg font-bold text-gray-900">Taskate</span>
                    </div>
                    <p class="text-sm text-gray-500 max-w-xs leading-relaxed">Organize your life beautifully. A modern task management app built for focus and creativity.</p>
                </div>
                <div>
                    <h5 class="font-semibold text-gray-900 text-sm mb-3">Product</h5>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#features" class="hover:text-brand-600 transition">Features</a></li>
                        <li><a href="{{ url('/dashboard') }}" class="hover:text-brand-600 transition">Dashboard</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold text-gray-900 text-sm mb-3">Resources</h5>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-brand-600 transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition">Blog</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition">Guides</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold text-gray-900 text-sm mb-3">Legal</h5>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-brand-600 transition">Terms</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition">Privacy</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">&copy; {{ date('Y') }} Taskate. All rights reserved.</div>
            </div>
        </div>
    </footer>

    <!-- Scroll reveal -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const obs = new IntersectionObserver(entries => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));
        });
    </script>
</body>
</html>
