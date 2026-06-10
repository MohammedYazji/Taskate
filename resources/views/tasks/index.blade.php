<x-app-layout>
    <div class="max-w-4xl">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">My Tasks</h1>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tasks as $task)
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
                <div class="w-5 h-5 rounded border-2 border-gray-300 flex-shrink-0"></div>
                <span class="flex-1 text-sm text-gray-800">{{ $task->title }}</span>
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

    </div>
</x-app-layout>
