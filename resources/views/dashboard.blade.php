<x-app-layout>
    <div>
    <div class="flex gap-6">

        {{-- Left Column --}}
        <div class="flex-1 min-w-0">

            {{-- Greeting --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                    {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ now()->format('l, F j') }} ·
                    @if($tasksDueToday > 0)
                        You have {{ $tasksDueToday }} task{{ $tasksDueToday > 1 ? 's' : '' }} due today
                    @else
                        No tasks due today 🎉
                    @endif
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">

                {{-- Total --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Total Tasks</span>
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalTasks }}</p>
                    <p class="text-xs text-gray-400 mt-1">+3 this week</p>
                </div>

                {{-- Completed --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Completed</span>
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $completedTasks }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% completion rate
                    </p>
                </div>

                {{-- Overdue --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Overdue</span>
                        <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold {{ $overdueTasks > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $overdueTasks }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $overdueTasks > 0 ? $overdueTasks . ' high priority' : 'All caught up!' }}
                    </p>
                </div>

            </div>

            {{-- My Tasks --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">My Tasks</h2>
                    <div class="flex items-center gap-2">
                        <button class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Filter</button>
                        <button class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Sort</button>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($tasks as $task)
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition">

                        {{-- Checkbox --}}
                        <div class="w-5 h-5 rounded border-2 flex-shrink-0
                            {{ $task->status === \App\Enums\TaskStatus::Done
                                ? 'bg-violet-600 border-violet-600'
                                : 'border-gray-300' }}">
                        </div>

                        {{-- Title + Project --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                {{ $task->title }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">No project</p>
                        </div>

                        {{-- Priority --}}
                        @php
                            $colors = [
                                'high'   => 'text-red-600 bg-red-50 border-red-100',
                                'medium' => 'text-orange-500 bg-orange-50 border-orange-100',
                                'low'    => 'text-green-600 bg-green-50 border-green-100',
                            ];
                            $color = $colors[$task->priority->value] ?? 'text-gray-500 bg-gray-100';
                        @endphp
                        <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $color }}">
                            {{ ucfirst($task->priority->value) }}
                        </span>

                        {{-- Due Date --}}
                        @php $isDueToday = $task->due_date && $task->due_date->isToday(); @endphp
                        <div class="flex items-center gap-1 text-xs {{ $isDueToday ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            @if($isDueToday)
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                            </svg>
                            <span class="text-red-500 text-[10px] uppercase tracking-wider">Today</span>
                            @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $task->due_date ? $task->due_date->format('M j') : '—' }}
                            @endif
                        </div>

                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        No tasks yet — create your first one!
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column - Calendar --}}
        <div class="w-72 flex-shrink-0 space-y-4">

            {{-- Calendar --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">{{ now()->format('F Y') }}</h3>
                    <div class="flex items-center gap-1">
                        <button class="p-1 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="p-1 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Day headers --}}
                <div class="grid grid-cols-7 mb-2">
                    @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $day)
                    <div class="text-center text-xs text-gray-400 py-1">{{ $day }}</div>
                    @endforeach
                </div>

                {{-- Calendar days --}}
                @php
                    $startOfMonth = now()->startOfMonth();
                    $daysInMonth = now()->daysInMonth;
                    $startDow = $startOfMonth->dayOfWeekIso - 1;
                @endphp
                <div class="grid grid-cols-7 gap-y-1">
                    @for($i = 0; $i < $startDow; $i++)
                        <div></div>
                    @endfor
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        <div class="flex items-center justify-center">
                            <span class="w-7 h-7 flex items-center justify-center text-xs rounded-full
                                {{ $day === now()->day ? 'bg-violet-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100 cursor-pointer' }}">
                                {{ $day }}
                            </span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Upcoming --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Upcoming</h3>
                <div class="space-y-2">
                    @forelse($tasks->whereNotNull('due_date')->where('due_date', '>=', now()->toDateString())->sortBy('due_date')->take(4) as $task)
                    <div class="flex items-start gap-2">
                        <div class="w-1 h-8 rounded-full bg-violet-500 flex-shrink-0 mt-0.5"></div>
                        <div>
                            <p class="text-xs font-medium text-gray-800">{{ $task->title }}</p>
                            <p class="text-xs text-gray-400">{{ $task->due_date->format('M j') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No upcoming tasks</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    </div>

</x-app-layout>
