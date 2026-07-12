<x-app-layout>
    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ $query ? "Search results for \"{$query}\"" : 'Search' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }} found</p>
        </div>

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
                <div class="flex-1 min-w-0">
                    <p class="text-sm {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through text-gray-400' : 'text-gray-800' }}">
                        {{ $task->title }}
                    </p>
                    @if($task->project)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $task->project->name }}</p>
                    @endif
                </div>
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
                {{ $query ? "No tasks match \"{$query}\"" : 'Type a query to search tasks' }}
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
