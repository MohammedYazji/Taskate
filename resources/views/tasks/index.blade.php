<x-app-layout>
    <div x-data="{ open: false, filterOpen: false }" x-on:open-task-panel.document="open = true" class="max-w-4xl">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Tasks</h1>
            <button @click="open = true"
                class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <span class="text-lg leading-none">+</span> New Task
            </button>
        </div>

        {{-- Filter & Sort --}}
        <div class="flex items-center gap-2 mb-4">
            <div class="relative" @click.outside="filterOpen = false">
                <button @click="filterOpen = !filterOpen"
                    class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if(request()->hasAny(['priority', 'status', 'date']))
                        <span class="w-1.5 h-1.5 bg-violet-600 rounded-full"></span>
                    @endif
                </button>
                <div x-show="filterOpen" x-cloak
                    class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 min-w-48 z-10">
                    <form method="GET" action="{{ route('tasks.index') }}" id="filter-form">
                        @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                                <select name="priority" class="w-full text-xs border border-gray-200 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-violet-500" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                <select name="status" class="w-full text-xs border border-gray-200 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-violet-500" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>Todo</option>
                                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                            @if(request('date'))
                            <input type="hidden" name="date" value="{{ request('date') }}">
                            @endif
                            @if(request()->hasAny(['priority', 'status', 'date']))
                            <a href="{{ route('tasks.index') }}" class="block text-xs text-violet-600 hover:text-violet-700 mt-2">Clear filters</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative" x-data="{ sortOpen: false }" @click.outside="sortOpen = false">
                <button @click="sortOpen = !sortOpen"
                    class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6M3 12h12M3 17h8"/>
                    </svg>
                    Sort
                    @if(request('sort'))
                        <span class="w-1.5 h-1.5 bg-violet-600 rounded-full"></span>
                    @endif
                </button>
                <div x-show="sortOpen" x-cloak
                    class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 min-w-48 z-10">
                    <form method="GET" action="{{ route('tasks.index') }}" id="sort-form">
                        @if(request()->hasAny(['priority', 'status', 'date']))
                        @foreach(['priority', 'status', 'date'] as $f)
                        @if(request($f))
                        <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                        @endif
                        @endforeach
                        @endif
                        <div class="space-y-1">
                            @foreach([
                                '' => 'Latest',
                                'priority' => 'Priority',
                                'date_asc' => 'Due date (asc)',
                                'date_desc' => 'Due date (desc)',
                            ] as $val => $label)
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="sort" value="{{ $val }}"
                                    {{ request('sort', '') === $val ? 'checked' : '' }}
                                    class="text-violet-600 focus:ring-violet-500"
                                    onchange="this.form.submit()">
                                <span class="text-xs text-gray-600">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tasks as $task)
            @php $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done; @endphp
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition {{ $isOverdue ? 'bg-red-50/40' : '' }}">
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
                <span class="flex-1 text-sm {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-gray-400' : 'text-gray-800' }}">{{ $task->title }}</span>
                @php
                    $colors = [
                        'high'   => 'text-red-500 bg-red-50',
                        'medium' => 'text-orange-500 bg-orange-50',
                        'low'    => 'text-green-600 bg-green-50'
                    ];
                    $color = $colors[$task->priority->value] ?? 'text-gray-500 bg-gray-100';
                @endphp
                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $color }} uppercase tracking-wide">
                    {{ $task->priority->value }}
                </span>
                <span class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                    {{ $task->due_date ? $task->due_date->format('M j') : '—' }}
                </span>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                No tasks yet — create your first one!
            </div>
            @endforelse
        </div>

        {{-- Overlay --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 bg-black/30 z-40">
        </div>

        {{-- Slide-in Panel --}}
        <div x-show="open" x-cloak @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">

            {{-- Panel Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">New Task</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <x-task-form :tags="$tags" :projects="$projects" />
        </div>

    </div>
</x-app-layout>
