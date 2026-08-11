@props(['task'])

<div class="relative flex-shrink-0 opacity-0 group-hover:opacity-100 transition" x-data="{
    open: false,
    sub: null,
    moveOpen: false,
    tagOpen: false,
    subtaskTitle: '',
    parentTaskId: '',
    moveProjectId: '',
    projects: [],
    tags: [],
    taskTags: @js(collect($task->tags ?? [])->pluck('id')->toArray()),
    init() {
        fetch('/api/projects', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => this.projects = d);
        fetch('/api/tags', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => this.tags = d);
    },
    csrf() { return document.querySelector('meta[name=csrf-token]').content; },
    patch(url, body) {
        return fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        }).then(() => location.reload());
    },
    del(url) {
        return fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': this.csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        }).then(() => location.reload());
    },
    post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
    },
    setDueDate(date) {
        this.patch('/tasks/{{ $task->id }}', { due_date: date });
    },
    setPriority(p) {
        this.patch('/tasks/{{ $task->id }}', { priority: p });
    },
    addSubtask() {
        if (!this.subtaskTitle.trim()) return;
        this.post('/tasks/{{ $task->id }}/subtasks', { title: this.subtaskTitle.trim() }).then(() => location.reload());
    },
    linkParent() {
        if (!this.parentTaskId) return;
        this.patch('/tasks/{{ $task->id }}', { parent_task_id: this.parentTaskId }).then(() => location.reload());
    },
    markWonDo() {
        this.patch('/tasks/{{ $task->id }}', { status: 'done' });
    },
    moveTo() {
        if (!this.moveProjectId) return;
        this.patch('/tasks/{{ $task->id }}', { project_id: this.moveProjectId, section_id: null });
    },
    toggleTag(tagId) {
        const idx = this.taskTags.indexOf(tagId);
        if (idx > -1) {
            this.taskTags.splice(idx, 1);
        } else {
            this.taskTags.push(tagId);
        }
        this.patch('/tasks/{{ $task->id }}', { tag_ids: this.taskTags });
    },
    duplicateTask() {
        this.post('/tasks', {
            title: '{{ addslashes($task->title) }}',
            project_id: @js($task->project_id),
            section_id: @js($task->section_id),
            priority: @js($task->priority?->value),
            due_date: @js($task->due_date?->format('Y-m-d')),
            status: 'todo',
        }).then(() => location.reload());
    },
    deleteTask() {
        if (!confirm('Delete this task?')) return;
        this.del('/tasks/{{ $task->id }}');
    }
}" @click.outside="open = false; sub = null; moveOpen = false; tagOpen = false">
    <button @click.stop="open = !open; sub = null" class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-[60] py-1 w-52">

        {{-- Date --}}
        <div class="relative" @click.outside="sub = null">
            <button @click.stop="sub = sub === 'date' ? null : 'date'" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Date
                <svg class="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="sub === 'date'" x-cloak
                class="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-44">
                <button @click="setDueDate('{{ now()->toDateString() }}'); open = false" class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Today</button>
                <button @click="setDueDate('{{ now()->addDay()->toDateString() }}'); open = false" class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Tomorrow</button>
                <button @click="setDueDate('{{ now()->addWeek()->startOfWeek(Carbon\Carbon::MONDAY)->toDateString() }}'); open = false" class="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Next Week</button>
                <hr class="my-1 border-gray-100">
                <div class="px-3 py-1.5">
                    <input type="date" @change="setDueDate($event.target.value); open = false"
                        class="w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1 outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        {{-- Priority --}}
        <div class="relative" @click.outside="sub = null">
            <button @click.stop="sub = sub === 'priority' ? null : 'priority'" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                Priority
                <svg class="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="sub === 'priority'" x-cloak
                class="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-36">
                <button @click="setPriority('low')" class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                    :class="'{{ $task->priority?->value }}' === 'low' ? 'text-green-600 font-semibold bg-green-50' : 'text-gray-600'">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Low
                </button>
                <button @click="setPriority('medium')" class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                    :class="'{{ $task->priority?->value }}' === 'medium' ? 'text-orange-500 font-semibold bg-orange-50' : 'text-gray-600'">
                    <span class="w-2 h-2 rounded-full bg-orange-400"></span> Medium
                </button>
                <button @click="setPriority('high')" class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                    :class="'{{ $task->priority?->value }}' === 'high' ? 'text-red-500 font-semibold bg-red-50' : 'text-gray-600'">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span> High
                </button>
                <hr class="my-1 border-gray-100">
                <button @click="setPriority('')" class="w-full text-left px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 transition">Remove</button>
            </div>
        </div>

        <hr class="my-1 border-gray-100">

        {{-- Add subtask --}}
        <button @click.stop="sub = sub === 'subtask' ? null : 'subtask'" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add subtask
        </button>
        <div x-show="sub === 'subtask'" x-cloak class="px-3 pb-2">
            <div class="flex gap-1.5">
                <input type="text" x-model="subtaskTitle" @keydown.enter="addSubtask()" placeholder="Subtask title..."
                    class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-1 focus:ring-brand-500">
                <button @click="addSubtask()" class="text-xs px-2 py-1.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition">Add</button>
            </div>
        </div>

        {{-- Link parent task --}}
        <button @click.stop="sub = sub === 'parent' ? null : 'parent'" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            Link parent task
        </button>
        <div x-show="sub === 'parent'" x-cloak class="px-3 pb-2">
            <div class="flex gap-1.5">
                <input type="number" x-model="parentTaskId" placeholder="Task ID..."
                    class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-1 focus:ring-brand-500">
                <button @click="linkParent()" class="text-xs px-2 py-1.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition">Link</button>
            </div>
        </div>

        <hr class="my-1 border-gray-100">

        {{-- Tags --}}
        <div class="relative" @click.outside="tagOpen = false">
            <button @click.stop="tagOpen = !tagOpen" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                Tags
                <svg class="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="tagOpen" x-cloak
                class="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-44 max-h-48 overflow-y-auto">
                <template x-for="tag in tags" :key="tag.id">
                    <button @click="toggleTag(tag.id)" class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                        :class="taskTags.includes(tag.id) ? 'font-semibold' : 'text-gray-600'">
                        <div class="w-2 h-2 rounded-full flex-shrink-0" :style="'background-color:' + tag.color"></div>
                        <span class="flex-1 text-left truncate" x-text="tag.name"></span>
                        <svg x-show="taskTags.includes(tag.id)" class="w-3 h-3 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </button>
                </template>
                <div x-show="tags.length === 0" class="px-3 py-2 text-xs text-gray-400">No tags yet</div>
            </div>
        </div>

        {{-- Move to --}}
        <div class="relative" @click.outside="moveOpen = false">
            <button @click.stop="moveOpen = !moveOpen" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                Move to
                <svg class="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="moveOpen" x-cloak
                class="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-48 max-h-60 overflow-y-auto">
                <template x-for="p in projects.filter(p => p.id != {{ $task->project_id }})" :key="p.id">
                    <button @click="moveProjectId = p.id; moveTo()" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                        <span x-text="p.icon || '📁'"></span>
                        <span class="flex-1 text-left truncate" x-text="p.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <hr class="my-1 border-gray-100">

        {{-- Duplicate --}}
        <button @click="duplicateTask()" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            Duplicate
        </button>

        <hr class="my-1 border-gray-100">

        {{-- Delete --}}
        <button @click="deleteTask()" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete
        </button>
    </div>
</div>
