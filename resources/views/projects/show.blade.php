<x-app-layout>
    @php
        $ungroupedTasks = $tasks->filter(fn($t) => !$t->section_id);
    @endphp

    <div x-data="{
        newOpen: false,
        newTitle: '',
        newPriority: 'medium',
        newDate: '',
        newSectionId: @js($sections->first()?->id),
        sectionNames: @js($sections->pluck('name', 'id')->toArray()),
        sections: {
            @foreach($sections as $section)
            {{ $section->id }}: localStorage.getItem('project_{{ $project->id }}_section_{{ $section->id }}') !== 'false',
            @endforeach
        },
        toggleSection(id) {
            this.sections[id] = !this.sections[id];
            localStorage.setItem('project_{{ $project->id }}_section_' + id, this.sections[id]);
        },
        sortOpen: false,
        menuOpen: false,
        addingSection: false,
        newSectionName: '',
        editingSectionId: null,
        editingSectionName: '',
        sectionMenuId: null,
        editOpen: false,
        editTask: {},
        deleteSectionId: null,
        deleteSectionTarget: '',
        moveSectionId: null,
        moveProjectId: '',
        moveSectionTarget: '',
        sectionModalOpen: false,
        sectionModalName: '',
        sectionModalMode: '',
        sectionModalParentId: null,
        tasksData: @js($tasks->values()->map(fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'status' => $t->status->value,
            'priority' => $t->priority->value,
            'due_date' => $t->due_date?->format('Y-m-d'),
            'description' => $t->description,
            'is_recurring' => $t->is_recurring,
            'section_id' => $t->section_id,
            'tag_ids' => $t->tags->pluck('id')->toArray(),
            'subtasks' => $t->subtasks->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_completed' => $s->is_completed])->toArray(),
            'comments' => $t->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user->name ?? '', 'created_at' => $c->created_at->diffForHumans()])->toArray(),
        ])->toArray()),
        openEdit(taskId) {
            const t = this.tasksData.find(t => t.id === taskId);
            if (t) { this.editTask = { ...t }; this.editOpen = true; }
        },
        syncTask() {
            const idx = this.tasksData.findIndex(t => t.id === this.editTask.id);
            if (idx !== -1) this.tasksData[idx] = { ...this.editTask };
        },

        createSection() {
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
            .then(r => r.json())
            .then(section => {
                this.sections[section.id] = true;
                window.location.reload();
            });
            this.newSectionName = '';
            this.addingSection = false;
        },
        renameSection(id) {
            if (!this.editingSectionName.trim()) return;
            fetch('/sections/' + id, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ name: this.editingSectionName.trim() }),
            })
            .then(() => window.location.reload());
        },
        deleteSection(id) {
            this.deleteSectionId = id;
            this.deleteSectionTarget = '';
            this.sectionMenuId = null;
        },
        confirmDeleteSection() {
            fetch('/sections/' + this.deleteSectionId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ target_section_id: this.deleteSectionTarget || null }),
            })
            .then(() => window.location.reload());
        },
        addSectionAbove(sectionId) {
            this.sectionModalMode = 'above';
            this.sectionModalParentId = sectionId;
            this.sectionModalName = '';
            this.sectionModalOpen = true;
            this.sectionMenuId = null;
        },
        addSectionBelow(sectionId) {
            this.sectionModalMode = 'below';
            this.sectionModalParentId = sectionId;
            this.sectionModalName = '';
            this.sectionModalOpen = true;
            this.sectionMenuId = null;
        },
        submitSectionModal() {
            if (!this.sectionModalName.trim()) return;
            const url = this.sectionModalMode === 'above'
                ? '/sections/' + this.sectionModalParentId + '/above'
                : '/sections/' + this.sectionModalParentId + '/below';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ name: this.sectionModalName.trim() }),
            })
            .then(() => window.location.reload());
        },
        openMoveSection(sectionId) {
            this.moveSectionId = sectionId;
            this.moveProjectId = '';
            this.moveSectionTarget = '';
            this.sectionMenuId = null;
        },
        confirmMoveSection() {
            const targetProjectId = this.moveProjectId;
            const targetSectionId = this.moveSectionTarget || null;
            fetch('/sections/' + this.moveSectionId + '/move', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ project_id: targetProjectId, section_id: targetSectionId }),
            })
            .then(() => window.location.reload());
        },
        sectionCount(id) {
            return this.tasksData.filter(t => String(t.section_id) === String(id)).length;
        },
        toggleTask(taskId) {
            const t = this.tasksData.find(t => t.id === taskId);
            if (!t) return;
            const newStatus = t.status === 'done' ? 'todo' : 'done';
            fetch('/tasks/' + taskId + '/toggle', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(() => { t.status = newStatus; });
        },
        openNewTask(sectionId) {
            this.newSectionId = sectionId;
            this.newOpen = true;
        },
        quickTitle: '',
        quickPriority: 'medium',
        quickDate: '',
        quickSectionId: @js($sections->first()?->id),
        quickTagIds: [],
        quickMenuOpen: false,
        addQuickTask() {
            if (!this.quickTitle.trim()) return;
            const sectionId = this.quickSectionId;
            fetch('/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    title: this.quickTitle.trim(),
                    project_id: {{ $project->id }},
                    section_id: sectionId,
                    priority: this.quickPriority,
                    status: 'todo',
                    due_date: this.quickDate || null,
                    tag_ids: this.quickTagIds,
                }),
            })
            .then(r => r.json())
            .then(task => {
                this.tasksData.push({
                    id: task.id,
                    title: task.title,
                    status: task.status,
                    priority: task.priority,
                    due_date: task.due_date,
                    description: task.description || '',
                    is_recurring: task.is_recurring,
                    section_id: task.section_id,
                    tag_ids: this.quickTagIds,
                    subtasks: [],
                    comments: [],
                });
                this.quickTitle = '';
                this.quickPriority = 'medium';
                this.quickDate = '';
                this.quickSectionId = @js($sections->first()?->id);
                this.quickTagIds = [];
            });
        }
    }"
         x-on:date-picker-ok.window="
            if (newOpen) {
                newDate = $event.detail.date || '';
            }
         "
         class="max-w-4xl">

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

                {{-- Add section --}}
                <button @click="addingSection = true; $nextTick(() => $refs.sectionInput.focus())"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Add section">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </button>

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
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Group by</span>
                        </div>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">None</button>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Status</button>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Priority</button>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Section</button>
                        <hr class="my-1 border-gray-100">
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Sort by</span>
                        </div>
                        <button class="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Manual</button>
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
                        <a href="{{ route('projects.board', $project) }}" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            Board
                        </a>
                        <a href="{{ route('calendar', ['project_id' => $project->id]) }}" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Timeline
                        </a>
                        <hr class="my-1 border-gray-100">
                        {{-- Section 2: Display --}}
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Display</span>
                        </div>
                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Hide completed
                        </button>
                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Show details
                        </button>
                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            View
                        </button>
                        <hr class="my-1 border-gray-100">
                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 transition">
                            Delete project
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Quick add task --}}
        <div class="mb-4" x-data="{ quickMenuOpen: false }">
            <div class="flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2.5">
                <input type="text" x-model="quickTitle" @keydown.enter="addQuickTask()"
                    placeholder="Add a task..."
                    class="flex-1 text-sm text-gray-600 outline-none ring-0 border-0 bg-transparent placeholder-gray-400 focus:ring-0 focus:outline-none focus:border-0 focus:shadow-none">

                {{-- Calendar icon — triggers date picker --}}
                <div class="flex-shrink-0" @date-picker-ok.window="quickDate = $event.detail.date || ''">
                    <x-date-picker icon />
                </div>

                {{-- Dropdown arrow --}}
                <div class="relative flex-shrink-0" @click.outside="quickMenuOpen = false">
                    <button @click="quickMenuOpen = !quickMenuOpen" class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div x-show="quickMenuOpen" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-56">

                        {{-- Section 1: Priority --}}
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Define priority</span>
                        </div>
                        <button @click="quickPriority = 'low'; quickMenuOpen = false"
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-gray-50 transition"
                            :class="quickPriority === 'low' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-3.5 h-3.5 text-green-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            Low
                        </button>
                        <button @click="quickPriority = 'medium'; quickMenuOpen = false"
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-gray-50 transition"
                            :class="quickPriority === 'medium' ? 'bg-yellow-50 text-yellow-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-3.5 h-3.5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            Medium
                        </button>
                        <button @click="quickPriority = 'high'; quickMenuOpen = false"
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-gray-50 transition"
                            :class="quickPriority === 'high' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-3.5 h-3.5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            High
                        </button>

                        <hr class="my-1 border-gray-100">

                        {{-- Section 2: Project, Section, Tags --}}
                        <div class="px-3 py-1.5">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Project, Section & Tags</span>
                        </div>

                        {{-- Section selector --}}
                        <div class="px-3 py-1.5">
                            <label class="text-[10px] text-gray-400">Section</label>
                            <select x-model="quickSectionId"
                                class="w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1 mt-0.5 outline-none focus:ring-1 focus:ring-brand-500">
                                <option value="">None</option>
                                @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tags --}}
                        <div class="px-3 py-1.5">
                            <label class="text-[10px] text-gray-400">Tags</label>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($tags as $tag)
                                <label class="flex items-center gap-1 cursor-pointer" @click.stop>
                                    <input type="checkbox" value="{{ $tag->id }}"
                                        :checked="quickTagIds.includes({{ $tag->id }})"
                                        @change="
                                            if ($event.target.checked) { quickTagIds.push({{ $tag->id }}) } else { quickTagIds = quickTagIds.filter(id => id !== {{ $tag->id }}) }
                                        "
                                        class="rounded border-gray-300 text-brand-500">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                </label>
                                @endforeach
                                @if(count($tags) === 0)
                                <span class="text-[10px] text-gray-400">No tags</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task List --}}
        <div class="bg-white rounded-xl border border-gray-200">

            {{-- Sections --}}
            @foreach($sections as $section)
            <div class="{{ !$loop->last ? 'border-t border-gray-100' : '' }}">
                {{-- Section Header --}}
                <div class="flex items-center gap-2 px-4 py-2.5 group/section">
                    <button @click="toggleSection({{ $section->id }})" class="flex items-center gap-2 flex-1 min-w-0">
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 flex-shrink-0"
                             :class="sections[{{ $section->id }}] ? 'rotate-90' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                        <span x-show="editingSectionId !== {{ $section->id }}"
                            class="text-sm font-semibold text-gray-800 truncate">{{ $section->name }}</span>
                        <input x-show="editingSectionId === {{ $section->id }}" x-cloak
                            x-model="editingSectionName"
                            @keydown.enter="renameSection({{ $section->id }})"
                            @keydown.escape="editingSectionId = null"
                            @blur="renameSection({{ $section->id }})"
                            class="text-sm font-semibold text-gray-800 bg-white border border-gray-300 rounded px-1.5 py-0.5 outline-none ring-1 ring-brand-500">
                        <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-1.5 py-0.5 font-medium flex-shrink-0" x-text="sectionCount({{ $section->id }})"></span>
                    </button>

                    {{-- Section menu --}}
                    <div class="relative flex-shrink-0 opacity-0 group-hover/section:opacity-100 transition" @click.outside="sectionMenuId = null">
                        <button @click.stop="sectionMenuId = sectionMenuId === {{ $section->id }} ? null : {{ $section->id }}"
                            class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                            </svg>
                        </button>
                        <div x-show="sectionMenuId === {{ $section->id }}" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                            <button @click="editingSectionId = {{ $section->id }}; editingSectionName = '{{ $section->name }}'; sectionMenuId = null"
                                class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Rename</button>
                            <button @click="sectionMenuId = null; openNewTask({{ $section->id }})"
                                class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Add Task</button>
                            <hr class="my-1 border-gray-100">
                            <button @click="addSectionAbove({{ $section->id }})"
                                class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Add Section Above</button>
                            <button @click="addSectionBelow({{ $section->id }})"
                                class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Add Section Below</button>
                            <hr class="my-1 border-gray-100">
                            <button @click="openMoveSection({{ $section->id }})"
                                class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Move to...</button>
                            <hr class="my-1 border-gray-100">
                            <button @click="deleteSection({{ $section->id }})"
                                class="w-full text-left px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">Delete</button>
                        </div>
                    </div>

                    <button @click="openNewTask({{ $section->id }})"
                        class="flex-shrink-0 opacity-0 group-hover/section:opacity-100 transition p-1 text-gray-400 hover:text-gray-600 rounded"
                        title="Add task to section">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                </div>

                {{-- Section Tasks --}}
                <div x-show="sections[{{ $section->id }}]"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1">
                    <div class="sortable-tasks" data-section="{{ $section->id }}">
                    <template x-for="task in tasksData.filter(t => String(t.section_id) === String({{ $section->id }}))" :key="task.id">
                    <div class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition group cursor-pointer border-t border-gray-50 task-row"
                         :data-id="task.id" data-section="{{ $section->id }}"
                         @click="openEdit(task.id)">
                        <button @click.stop="toggleTask(task.id)"
                            class="w-[18px] h-[18px] rounded-[5px] border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition"
                            :class="task.status === 'done' ? 'border-transparent hover:opacity-80' : 'border-gray-300 hover:border-brand-400'"
                            :style="task.status === 'done' ? 'background-color: {{ $project->color }}' : ''">
                            <svg x-show="task.status === 'done'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <span x-show="task.icon" class="text-sm flex-shrink-0" x-text="task.icon"></span>
                        <span class="flex-1 text-sm"
                            :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'"
                            x-text="task.title"></span>
                        <span class="text-[11px] font-medium px-1.5 py-0.5 rounded border"
                            :class="{
                                'text-red-600 bg-red-50 border-red-100': task.priority === 'high',
                                'text-orange-500 bg-orange-50 border-orange-100': task.priority === 'medium',
                                'text-green-600 bg-green-50 border-green-100': task.priority === 'low',
                                'text-gray-500 bg-gray-100': !task.priority
                            }"
                            x-text="task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : ''"></span>
                        <span x-show="task.description" class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </span>
                    </div>
                    </template>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Add Section --}}
            <div class="border-t border-gray-100">
                <div x-show="!addingSection">
                    <button @click="addingSection = true; $nextTick(() => $refs.sectionInput.focus())"
                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Section
                    </button>
                </div>
                <div x-show="addingSection" x-cloak class="px-4 py-2.5">
                    <input x-ref="sectionInput"
                        x-model="newSectionName"
                        @keydown.enter="createSection()"
                        @keydown.escape="addingSection = false; newSectionName = ''"
                        @blur="if(newSectionName.trim()) createSection(); else addingSection = false"
                        type="text"
                        placeholder="Section name..."
                        class="w-full text-sm font-medium text-gray-800 outline-none ring-0 border-0 bg-transparent placeholder-gray-400">
                </div>
            </div>
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

                    {{-- Section selector --}}
                    <div class="relative" x-data="{ sectionDropdownOpen: false }" @click.outside="sectionDropdownOpen = false">
                        <button @click="sectionDropdownOpen = !sectionDropdownOpen"
                            class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span x-text="newSectionId ? (sectionNames[newSectionId] || '') : ''"></span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        <div x-show="sectionDropdownOpen" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-40">
                            @foreach($sections as $section)
                            <button @click="newSectionId = {{ $section->id }}; sectionDropdownOpen = false"
                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 transition"
                                :class="newSectionId === {{ $section->id }} ? 'bg-gray-50 text-gray-900 font-semibold' : 'text-gray-600'">
                                {{ $section->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex-1"></div>
                    <div class="flex items-center gap-1.5">
                        <x-date-picker icon />
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

        {{-- Edit Panel --}}
        <x-edit-panel :tags="$tags" />

        {{-- Delete Section Popup --}}
        <div x-show="deleteSectionId" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6"
                @click.outside="deleteSectionId = null">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Section</h3>
                <p class="text-sm text-gray-500 mb-4">Tasks in this section will be moved to:</p>
                <select x-model="deleteSectionTarget"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 mb-5">
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
                <div class="flex justify-end gap-2">
                    <button @click="deleteSectionId = null"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                    <button @click="confirmDeleteSection()"
                        class="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg transition">Delete</button>
                </div>
            </div>
        </div>

        {{-- Move Section Popup --}}
        <div x-show="moveSectionId" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6"
                @click.outside="moveSectionId = null">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Move Section</h3>
                <p class="text-sm text-gray-500 mb-1">All tasks will be moved to:</p>

                {{-- Project selector --}}
                <select x-model="moveProjectId"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 mb-3">
                    <option value="">Select project...</option>
                    @foreach($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                    @endforeach
                </select>

                {{-- Section selector (only if project selected) --}}
                <div x-show="moveProjectId" x-transition class="mb-5">
                    <select x-model="moveSectionTarget"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500">
                        <template x-for="proj in {{ $projects->toJson() }}">
                            <template x-if="String(proj.id) === String(moveProjectId)">
                                <template x-for="sec in proj.sections" :key="sec.id">
                                    <option :value="sec.id" x-text="sec.name"></option>
                                </template>
                            </template>
                        </template>
                    </select>
                </div>
                <div x-show="!moveProjectId" class="mb-5"></div>

                <div class="flex justify-end gap-2">
                    <button @click="moveSectionId = null"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                    <button @click="confirmMoveSection()" :disabled="!moveProjectId"
                        class="px-4 py-2 text-sm text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition disabled:opacity-40">Move</button>
                </div>
            </div>
        </div>

        {{-- Add Section Name Modal --}}
        <div x-show="sectionModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="sectionModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                @click.outside="sectionModalOpen = false">
                <div class="px-6 pt-6 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 text-center" x-text="sectionModalMode === 'above' ? 'Add Section Above' : 'Add Section Below'"></h3>
                </div>
                <div class="px-6 pb-6 space-y-4">
                    <input type="text" x-model="sectionModalName"
                        x-init="$watch('sectionModalOpen', v => { if(v) $nextTick(() => $el.focus()) })"
                        @keydown.enter="submitSectionModal()"
                        placeholder="Section name..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
                    <div class="flex gap-3">
                        <button @click="sectionModalOpen = false" type="button"
                            class="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button @click="submitSectionModal()" type="button"
                            class="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sortable-tasks').forEach(function(el) {
                new Sortable(el, {
                    group: 'project-tasks',
                    animation: 150,
                    ghostClass: 'opacity-30',
                    handle: '.task-row',
                    onEnd: function(evt) {
                        var taskId = parseInt(evt.item.dataset.id);
                        var newSection = evt.to.dataset.section || null;
                        var newPosition = evt.newIndex;
                        fetch('/tasks/' + taskId, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                section_id: newSection,
                                position: newPosition,
                            }),
                        });
                        var t = Alpine.evaluate(evt.item.closest('[x-data]'), 'tasksData').find(t => t.id === taskId);
                        if (t) { t.section_id = newSection; t.position = newPosition; }
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
