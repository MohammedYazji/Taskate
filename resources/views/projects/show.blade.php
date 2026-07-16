<x-app-layout>
    <div x-data="{
        editOpen: false,
        editTask: { id: null, title: '', description: '', priority: 'medium', due_date: '', is_recurring: false, tag_ids: [] },
        newOpen: false
    }"
         x-on:open-task-panel.document="newOpen = true"
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
                    class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <span class="text-lg leading-none">+</span> Add Task
                </button>
            </div>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tasks as $task)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition group">

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

                <span class="text-xs text-gray-400">
                    {{ $task->due_date ? $task->due_date->format('M j') : '—' }}
                </span>

                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                        @click.prevent="
                            editTask = {
                                id: {{ $task->id }},
                                title: {{ json_encode($task->title) }},
                                description: {{ json_encode($task->description) }},
                                priority: {{ json_encode($task->priority->value) }},
                                due_date: {{ json_encode($task->due_date ? $task->due_date->format('Y-m-d') : '') }},
                                is_recurring: {{ $task->is_recurring ? 'true' : 'false' }},
                                tag_ids: {{ json_encode($task->tags->pluck('id')->toArray()) }}
                            };
                            editOpen = true"
                        class="p-1.5 text-gray-300 hover:text-violet-500 hover:bg-violet-50 rounded-lg transition"
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

                <x-task-form alpine :tags="$tags" :projects="[$project]" x-bind:action="`/tasks/${editTask.id}`" />
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

                <x-task-form :tags="$tags" :projects="[$project]" />
            </div>
    </div>
</x-app-layout>
