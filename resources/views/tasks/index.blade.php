<x-app-layout>
    <div x-data="{ open: false }" x-on:open-task-panel.document="open = true" class="max-w-4xl">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Tasks</h1>
            <button @click="open = true"
                class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <span class="text-lg leading-none">+</span> New Task
            </button>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tasks as $task)
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
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
                <span class="text-xs text-gray-400">
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

            <x-task-form />
        </div>

    </div>
</x-app-layout>
