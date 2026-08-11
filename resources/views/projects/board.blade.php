<x-app-layout>
    @if(session('success'))
    <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div x-data="{
        sortOpen: false,
        menuOpen: false,
        addingSection: false,
        newSectionName: '',
        addingTaskSectionId: null,
        addingTaskTitle: '',
        addQuickTask(sectionId) {
            if (!this.addingTaskTitle.trim()) return;
            fetch('/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    title: this.addingTaskTitle.trim(),
                    project_id: {{ $project->id }},
                    section_id: sectionId,
                    status: 'todo',
                    priority: 'medium',
                }),
            }).then(() => location.reload());
            this.addingTaskTitle = '';
            this.addingTaskSectionId = null;
        },
        addSection() {
            if (!this.newSectionName.trim()) return;
            fetch('/projects/{{ $project->id }}/sections', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ name: this.newSectionName.trim() }),
            })
            .then(() => location.reload());
            this.newSectionName = '';
            this.addingSection = false;
        }
    }" class="max-w-4xl" @board-add-task.window="addingTaskSectionId = $event.detail.sectionId; $nextTick(() => $refs['taskInput' + $event.detail.sectionId]?.focus())">

        {{-- Project Header --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <button @click="$store.sidebar.toggle()" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition -ml-1">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="$store.sidebar.open ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span class="text-xl flex-shrink-0">{{ $project->icon }}</span>
                <h1 class="text-xl font-bold text-gray-900 truncate max-w-[400px]">{{ $project->name }}</h1>
                @if($project->description)
                <p class="text-sm text-gray-500">{{ $project->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">

                {{-- Filter icon --}}
                <div class="relative" @click.outside="sortOpen = false">
                    <button @click="sortOpen = !sortOpen" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                    </button>
                    <div x-show="sortOpen" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Sort by</span>
                        </div>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Priority</button>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Date</button>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Alphabetical</button>
                    </div>
                </div>

                {{-- Overflow menu --}}
                <div class="relative" @click.outside="menuOpen = false">
                    <button @click="menuOpen = !menuOpen" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                        </svg>
                    </button>
                    <div x-show="menuOpen" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                        {{-- Section 1: View --}}
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">View</span>
                        </div>
                        <a href="{{ route('projects.show', $project) }}" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                            List
                        </a>
                        <a href="{{ route('projects.board', $project) }}" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-brand-600 bg-brand-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            Board
                        </a>
                        <a href="{{ route('calendar', ['project_id' => $project->id]) }}" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Timeline
                        </a>
                        <hr class="my-1 border-gray-100">
                        <button @click="if(confirm('Delete this list?')) { fetch('/projects/{{ $project->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => window.location.href = '/dashboard') }"
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 transition">
                            Delete project
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Board Columns --}}
        <div class="flex gap-4 overflow-x-auto pb-4 items-start">
            @foreach($sections as $section)
            <div class="w-72 flex-shrink-0 flex flex-col">
                <div class="flex items-center justify-between mb-3" x-data="{ sectionMenuOpen: false, renaming: false, renameName: '{{ addslashes($section->name) }}' }">
                    <template x-if="!renaming">
                        <h3 class="text-sm font-semibold text-gray-700">{{ $section->name }}</h3>
                    </template>
                    <template x-if="renaming">
                        <input type="text" x-model="renameName" x-init="$nextTick(() => $el.focus())"
                            @keydown.enter="fetch('/sections/{{ $section->id }}', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ name: renameName }) }).then(() => location.reload())"
                            @keydown.escape="renaming = false; renameName = '{{ addslashes($section->name) }}'"
                            class="text-sm font-semibold text-gray-700 border border-brand-300 rounded px-1.5 py-0.5 outline-none focus:ring-2 focus:ring-brand-500 w-full">
                    </template>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5">{{ $section->tasks->count() }}</span>
                        <div class="relative" @click.outside="sectionMenuOpen = null">
                            <button @click.stop="sectionMenuOpen = sectionMenuOpen === {{ $section->id }} ? null : {{ $section->id }}"
                                class="p-0.5 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                            <div x-cloak x-show="sectionMenuOpen === {{ $section->id }}"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                                <button @click="renaming = true; sectionMenuOpen = null"
                                    class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Rename</button>
                                <button @click="sectionMenuOpen = null; $dispatch('board-add-task', { sectionId: {{ $section->id }} })"
                                    class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Add Task</button>
                                <hr class="my-1 border-gray-100">
                                <button @click="if(confirm('Delete this section? Tasks will be moved to ungrouped.')) { fetch('/sections/{{ $section->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ target_section_id: null }) }).then(() => location.reload()) }"
                                    class="w-full text-left px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 space-y-2 board-column" data-section-id="{{ $section->id }}">
                    @forelse($section->tasks as $task)
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
                            <span class="text-[10px] text-gray-400">{{ $task->due_date->format('M d') }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-xs text-gray-400 py-6">No tasks</div>
                    @endforelse
                </div>
            </div>
            @endforeach

            {{-- Add Section Button --}}
            <div class="w-72 flex-shrink-0">
                <div x-show="!addingSection">
                    <button @click="addingSection = true; $nextTick(() => $refs.newSectionInput.focus())"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border-2 border-dashed border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Section
                    </button>
                </div>
                <div x-show="addingSection" x-cloak class="bg-white rounded-lg border border-gray-200 p-3">
                    <input x-ref="newSectionInput" type="text" x-model="newSectionName"
                        @keydown.enter="addSection()" @keydown.escape="addingSection = false; newSectionName = ''"
                        placeholder="Section name..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent mb-2">
                    <div class="flex gap-2">
                        <button @click="addSection()"
                            class="flex-1 text-xs font-medium py-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">Add</button>
                        <button @click="addingSection = false; newSectionName = ''"
                            class="flex-1 text-xs font-medium py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.board-column').forEach(col => {
                new Sortable(col, {
                    group: 'board',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        const taskId = evt.item.dataset.id;
                        const sectionId = evt.to.dataset.sectionId || null;
                        const position = evt.newIndex;

                        fetch(`/tasks/${taskId}/move`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ section_id: sectionId, position })
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
