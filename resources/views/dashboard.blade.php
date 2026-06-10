<x-app-layout>
    <div x-data="{
        editOpen: false,
        editTask: { id: null, title: '', description: '', priority: 'medium', due_date: '' },
        newOpen: false
    }"
         x-on:open-task-panel.document="newOpen = true">
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
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition group">

                        {{-- Checkbox --}}
                        <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition
                                {{ $task->status === \App\Enums\TaskStatus::Done
                                    ? 'bg-violet-600 border-violet-600 hover:bg-violet-700'
                                    : 'border-gray-300 hover:border-violet-400' }}">
                                @if($task->status === \App\Enums\TaskStatus::Done)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                @endif
                            </button>
                        </form>

                        {{-- Title + Project --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                {{ $task->title }}
                            </p>
                            <p class="text-xs {{ $task->status === \App\Enums\TaskStatus::Done ? 'text-green-400' : 'text-gray-400' }} mt-0.5">No project</p>
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

                        {{-- Actions --}}
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                            <a href="#"
                                @click.prevent="
                                    editTask = {
                                        id: {{ $task->id }},
                                        title: {{ json_encode($task->title) }},
                                        description: {{ json_encode($task->description) }},
                                        priority: {{ json_encode($task->priority->value) }},
                                        due_date: {{ json_encode($task->due_date ? $task->due_date->format('Y-m-d') : '') }}
                                    };
                                    editOpen = true"
                                class="p-1.5 text-gray-300 hover:text-violet-500 hover:bg-violet-50 rounded-lg transition"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                    title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
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

    {{-- Edit Task Panel --}}
    <div x-show="editOpen" x-cloak @click="editOpen = false"
            class="fixed inset-0 bg-black/30 z-40"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
        </div>

        <div x-show="editOpen" x-cloak
            class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Edit Task</h2>
                <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <x-task-form alpine x-bind:action="`/tasks/${editTask.id}`" />
        </div>

    {{-- New Task Panel --}}
    <div x-show="newOpen" x-cloak @click="newOpen = false"
            class="fixed inset-0 bg-black/30 z-40"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
        </div>

        <div x-show="newOpen" x-cloak
            class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">New Task</h2>
                <button @click="newOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <x-task-form />
        </div>

    </div>

</x-app-layout>
