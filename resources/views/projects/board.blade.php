<x-app-layout>
    @if(session('success'))
    <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div x-data="{
        sprintId: {{ $sprintId ?: 'null' }},
        activeSprintId: {{ $activeSprint?->id ?: 'null' }},
        columns: ['todo', 'in_progress', 'done']
    }" class="h-full flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-gray-900 truncate max-w-[300px]">{{ $project->name }}</h1>
                <span class="w-px h-5 bg-gray-200"></span>
                <form method="GET" action="{{ route('projects.board', $project) }}" id="sprint-switcher">
                    <select name="sprint_id" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Backlog</option>
                        @foreach($sprints as $sprint)
                        <option value="{{ $sprint->id }}" {{ $sprintId == $sprint->id ? 'selected' : '' }}>
                            {{ $sprint->name }}
                            @if($sprint->status->value === 'active') (Active) @endif
                        </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <a href="{{ route('projects.sprints.index', $project) }}"
                class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg transition">
                Manage Sprints
            </a>
        </div>

        {{-- Board Columns --}}
        <div class="flex-1 flex gap-4 p-4 overflow-x-auto bg-gray-50">
            @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $key => $label)
            @php $tasks = $columns[$key] ?? collect(); @endphp
            <div class="flex-1 min-w-64 flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">{{ $label }}</h3>
                    <span class="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5">{{ $tasks->count() }}</span>
                </div>

                <div class="flex-1 space-y-2" data-status="{{ $key }}">
                    @forelse($tasks as $task)
                    <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm hover:shadow-md transition cursor-grab"
                         data-id="{{ $task->id }}">
                        <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                        @if($task->subtasks->count() > 0)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $task->subtasks->where('is_completed', true)->count() }}/{{ $task->subtasks->count() }} subtasks
                        </p>
                        @endif
                        @php
                            $colors = [
                                'high'   => 'text-red-600 bg-red-50',
                                'medium' => 'text-orange-500 bg-orange-50',
                                'low'    => 'text-green-600 bg-green-50',
                            ];
                            $color = $colors[$task->priority->value] ?? 'text-gray-500 bg-gray-100';
                        @endphp
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $color }} uppercase tracking-wide">
                                {{ $task->priority->value }}
                            </span>
                            @if($task->due_date)
                            <span class="text-[10px] text-gray-400">{{ $task->due_date->format('M j') }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-xs text-gray-400 py-6">No tasks</div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const columns = document.querySelectorAll('[data-status]');
            columns.forEach(col => {
                new Sortable(col, {
                    group: 'board',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        const taskId = evt.item.dataset.id;
                        const newStatus = evt.to.dataset.status;
                        const position = evt.newIndex;

                        fetch(`/tasks/${taskId}/move`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify({ status: newStatus, position })
                        }).then(res => {
                            if (!res.ok) location.reload();
                        }).catch(() => location.reload());
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
