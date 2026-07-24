<x-app-layout>
    <div x-data="{
        newOpen: false,
        newTitle: '',
        newPriority: 'medium',
        newDate: ''
    }"
         x-on:open-task-panel.document="newOpen = true"
         x-on:date-picker-ok.window="
            if (newOpen) {
                newDate = $event.detail.date || '';
            }
         "
         class="max-w-4xl">

        {{-- Project Header --}}
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $project->color }}"></span>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 truncate max-w-[400px]">{{ $project->name }}</h1>
                    @if($project->description)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $project->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.board', $project) }}"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg transition">
                    Board
                </a>
                <a href="{{ route('projects.sprints.index', $project) }}"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg transition">
                    Sprints
                </a>
                <a href="{{ route('projects.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg transition">
                    Back to Projects
                </a>
                <button @click="newOpen = true"
                    class="hover:text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 text-white"
                    style="background-color: {{ $project->color }}">
                    <span class="text-lg leading-none">+</span> Add Task
                </button>
            </div>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tasks as $task)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition group cursor-pointer"
                 @click="window.location.href = '/dashboard?edit={{ $task->id }}'">

                <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="inline" @click.stop>
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition
                        {{ $task->status === \App\Enums\TaskStatus::Done
                            ? 'border-transparent hover:opacity-80'
                            : 'border-gray-300 hover:opacity-80' }}"
                        @if($task->status === \App\Enums\TaskStatus::Done) style="background-color: {{ $project->color }}" @endif>
                        @if($task->status === \App\Enums\TaskStatus::Done)
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        @endif
                    </button>
                </form>

                <span class="flex-1 text-sm {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-gray-400' : 'text-gray-800' }}">
                    {{ $task->title }}
                </span>

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

                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                    <a href="/dashboard?edit={{ $task->id }}"
                        class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                        title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Are you sure?')">
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
                No tasks in this project yet
            </div>
            @endforelse
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

            <div x-show="newOpen" x-cloak @click.stop
                class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full">

                <div class="flex items-center gap-3 px-6 py-3 border-b border-gray-200 flex-shrink-0">
                    <button @click="newOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div class="w-px h-4 bg-gray-200"></div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <x-date-picker />
                    </div>
                    <div class="flex-1"></div>
                    <div class="relative" x-data="{ flagOpen: false }" @click.outside="flagOpen = false">
                        <button @click="flagOpen = !flagOpen" class="p-1.5 rounded-lg transition hover:bg-gray-50">
                            <svg class="w-4 h-4" :class="{
                                'text-green-500': newPriority === 'low',
                                'text-yellow-500': newPriority === 'medium',
                                'text-red-500': newPriority === 'high',
                                'text-gray-300': !newPriority
                            }" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <div x-show="flagOpen" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-32">
                            <button @click="newPriority = 'low'; flagOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                :class="newPriority === 'low' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600'">
                                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                Low
                            </button>
                            <button @click="newPriority = 'medium'; flagOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                :class="newPriority === 'medium' ? 'bg-yellow-50 text-yellow-600 font-semibold' : 'text-gray-600'">
                                <svg class="w-4 h-4 text-yellow-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                Medium
                            </button>
                            <button @click="newPriority = 'high'; flagOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                :class="newPriority === 'high' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600'">
                                <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                High
                            </button>
                        </div>
                    </div>
                </div>

                <x-task-form :tags="$tags" :projects="[$project]" />
            </div>
    </div>
</x-app-layout>
