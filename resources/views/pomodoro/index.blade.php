<x-app-layout>
<div x-data="pomodoroApp()" class="flex gap-8 h-full">

    {{-- Left Column: Timer --}}
    <div class="flex-1 flex flex-col items-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-8 self-start">Pomodoro Timer</h1>

        {{-- Timer Display --}}
        <div class="relative w-72 h-72 mb-8">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" fill="none" stroke="#f3f4f6" stroke-width="6"/>
                <circle cx="100" cy="100" r="90" fill="none"
                    :stroke="mode === 'work' ? '#8b5cf6' : mode === 'short_break' ? '#22c55e' : '#3b82f6'"
                    stroke-width="6" stroke-linecap="round"
                    :stroke-dasharray="circumference" :stroke-dashoffset="progress"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-6xl font-bold tabular-nums"
                      :class="mode === 'work' ? 'text-gray-900' : mode === 'short_break' ? 'text-green-600' : 'text-blue-600'"
                      x-text="timeDisplay"></span>
                <span class="text-xs font-medium uppercase tracking-wider mt-2"
                      :class="mode === 'work' ? 'text-gray-400' : mode === 'short_break' ? 'text-green-400' : 'text-blue-400'"
                      x-text="modeLabel"></span>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex items-center justify-center gap-3 mb-6">
            <button @click="reset()"
                class="p-3 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <button @click="running ? pause() : start()"
                class="px-10 py-3 rounded-xl text-sm font-semibold text-white transition shadow-lg shadow-brand-400/30"
                :class="running ? 'bg-amber-500 hover:bg-amber-600' : mode === 'work' ? 'bg-brand-500 hover:bg-brand-600' : mode === 'short_break' ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-500 hover:bg-blue-600'">
                <span x-text="running ? 'Pause' : 'Start'"></span>
            </button>
            <button @click="skip()"
                class="p-3 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Mode Tabs --}}
        <div class="flex items-center justify-center gap-1 bg-gray-100 rounded-xl p-1 w-full max-w-sm mb-8">
            <button @click="switchMode('work')"
                class="flex-1 px-4 py-2 text-xs font-semibold rounded-lg transition capitalize"
                :class="mode === 'work' ? 'bg-white text-brand-500 shadow-sm' : 'text-gray-500 hover:text-gray-700'">Focus (25m)</button>
            <button @click="switchMode('short_break')"
                class="flex-1 px-4 py-2 text-xs font-semibold rounded-lg transition capitalize"
                :class="mode === 'short_break' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">Short Break (5m)</button>
            <button @click="switchMode('long_break')"
                class="flex-1 px-4 py-2 text-xs font-semibold rounded-lg transition capitalize"
                :class="mode === 'long_break' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">Long Break (15m)</button>
        </div>

        {{-- Today's Stats --}}
        <div class="grid grid-cols-3 gap-3 w-full max-w-sm">
            <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-brand-500" x-text="stats.work_count || 0"></div>
                <div class="text-[10px] text-gray-500 mt-0.5">Sessions</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-green-500" x-text="stats.short_break_count || 0"></div>
                <div class="text-[10px] text-gray-500 mt-0.5">Breaks</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-amber-500" x-text="(stats.total_minutes || 0) + 'm'"></div>
                <div class="text-[10px] text-gray-500 mt-0.5">Focus Time</div>
            </div>
        </div>
    </div>

    {{-- Right Column: Task Picker + Edit Panel --}}
    <div class="w-[400px] flex-shrink-0 flex flex-col gap-5 overflow-y-auto pb-6 pr-2">

        {{-- Task Picker --}}
        <div @click.outside="pickerOpen = false">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Current Task</label>
            <div class="relative">
                <button @click="pickerOpen = !pickerOpen"
                    class="w-full flex items-center gap-3 border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white hover:border-gray-300 transition text-left">
                    <template x-if="selectedTask">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" :style="'background-color:' + (selectedTask.project?.color || '#8b5cf6')"></div>
                            <span class="text-gray-900 truncate" x-text="selectedTask.title"></span>
                            <span class="text-xs text-gray-400 flex-shrink-0" x-text="selectedTask.project?.name || 'No project'"></span>
                        </div>
                    </template>
                    <template x-if="!selectedTask">
                        <span class="text-gray-400">No task selected (free focus)</span>
                    </template>
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="pickerOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="pickerOpen" x-cloak
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    class="absolute top-full left-0 right-0 z-50 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-80 flex flex-col">
                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="taskSearch" placeholder="Search tasks..."
                            class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="flex items-center gap-0.5 px-2 py-1.5 border-b border-gray-100">
                        <button @click="taskGroupBy = 'date'" class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition"
                            :class="taskGroupBy === 'date' ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-600'">Date</button>
                        <button @click="taskGroupBy = 'project'" class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition"
                            :class="taskGroupBy === 'project' ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-600'">Project</button>
                        <button @click="taskGroupBy = 'tag'" class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition"
                            :class="taskGroupBy === 'tag' ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-600'">Tag</button>
                    </div>
                    <div class="overflow-y-auto flex-1 p-1">
                        <template x-for="group in filteredGroups" :key="group.label">
                            <div>
                                <div class="flex items-center gap-2 px-2 py-1.5">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="group.label"></span>
                                    <span class="text-[10px] text-gray-300" x-text="'(' + group.tasks.length + ')'"></span>
                                </div>
                                <template x-for="task in group.tasks" :key="task.id">
                                    <button @click="selectTask(task); pickerOpen = false; taskSearch = ''"
                                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition"
                                        :class="selectedTaskId == task.id ? 'bg-brand-50 ring-1 ring-brand-400/30' : 'hover:bg-gray-50'">
                                        <div class="w-2 h-2 rounded-full flex-shrink-0" :style="'background-color:' + (task.project?.color || '#8b5cf6')"></div>
                                        <span class="flex-1 text-sm truncate"
                                            :class="selectedTaskId == task.id ? 'text-brand-600 font-medium' : 'text-gray-700'"
                                            x-text="task.title"></span>
                                        <template x-if="task.project">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500 flex-shrink-0" x-text="task.project.name"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <div x-show="filteredGroups.length === 0" class="p-4 text-center text-xs text-gray-400">No matching tasks</div>
                    </div>
                    <div x-show="selectedTaskId" class="p-2 border-t border-gray-100">
                        <button @click="selectedTaskId = ''; selectedTask = null; pickerOpen = false"
                            class="w-full text-center text-xs text-gray-400 hover:text-gray-600 py-1 transition">Clear selection</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Edit Panel --}}
        <template x-if="selectedTask">
            <div class="bg-white border border-gray-200 rounded-xl flex flex-col" x-data="{
                menuOpen: false,
                commentsOpen: false,
                saveTagTimer: null,
                saveField() {
                    clearTimeout(this.saveTagTimer);
                    this.saveTagTimer = setTimeout(() => {
                        fetch('/tasks/' + selectedTask.id, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({
                                title: selectedTask.title,
                                priority: selectedTask.priority,
                                due_date: selectedTask.due_date || null,
                            }),
                        });
                    }, 500);
                }
            }">

                {{-- Header: checkbox + title --}}
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                    <button @click="
                        selectedTask.status = selectedTask.status === 'done' ? 'todo' : 'done';
                        fetch('/tasks/' + selectedTask.id + '/toggle', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                        });
                    " class="w-5 h-5 rounded-[5px] border-2 flex-shrink-0 flex items-center justify-center transition"
                        :class="selectedTask.status === 'done' ? 'bg-brand-500 border-brand-500' : 'border-gray-300 hover:border-brand-400'">
                        <svg x-show="selectedTask.status === 'done'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                    <input type="text" x-model="selectedTask.title" @blur="saveField()" @keydown.enter="$el.blur()"
                        class="flex-1 text-base font-semibold text-gray-900 outline-none border-0 bg-transparent placeholder-gray-300"
                        placeholder="Task title...">
                </div>

                {{-- Body: title input, priority, date --}}
                <div class="px-5 py-4 space-y-4">
                    <div class="flex items-center gap-3">
                        {{-- Date --}}
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="date" x-model="selectedTask.due_date" @change="saveField()"
                                class="text-xs text-gray-600 outline-none border-0 bg-transparent cursor-pointer">
                        </div>
                        <div class="flex-1"></div>
                        {{-- Priority --}}
                        <div class="relative" x-data="{ flagOpen: false }" @click.outside="flagOpen = false">
                            <button @click="flagOpen = !flagOpen" class="p-1.5 rounded-lg transition hover:bg-gray-50">
                                <svg class="w-4 h-4" :class="{
                                    'text-green-500': selectedTask.priority === 'low',
                                    'text-yellow-500': selectedTask.priority === 'medium',
                                    'text-red-500': selectedTask.priority === 'high',
                                    'text-gray-300': !selectedTask.priority
                                }" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                            <div x-show="flagOpen" x-cloak x-transition class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-32">
                                <button @click="selectedTask.priority = 'low'; flagOpen = false; saveField()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                    :class="selectedTask.priority === 'low' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600'">
                                    <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                    Low
                                </button>
                                <button @click="selectedTask.priority = 'medium'; flagOpen = false; saveField()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                    :class="selectedTask.priority === 'medium' ? 'bg-yellow-50 text-yellow-600 font-semibold' : 'text-gray-600'">
                                    <svg class="w-4 h-4 text-yellow-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                    Medium
                                </button>
                                <button @click="selectedTask.priority = 'high'; flagOpen = false; saveField()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                                    :class="selectedTask.priority === 'high' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600'">
                                    <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                    High
                                </button>
                            </div>
                        </div>
                        {{-- Three dots --}}
                        <div class="relative">
                            <button @click="menuOpen = !menuOpen"
                                class="p-1.5 rounded-lg transition"
                                :class="menuOpen ? 'bg-brand-50 text-brand-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false" x-transition
                                class="absolute top-full right-0 mt-1 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-10 max-h-64 overflow-y-auto">
                                {{-- Tags --}}
                                <div class="p-3 border-b border-gray-100">
                                    <h4 class="text-[10px] font-semibold text-gray-500 mb-2 uppercase tracking-wide">Tags</h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($tags as $tag)
                                        <label class="flex items-center gap-1.5 cursor-pointer" @click.stop>
                                            <input type="checkbox" value="{{ $tag->id }}"
                                                :checked="selectedTask && selectedTask.tag_ids && selectedTask.tag_ids.includes({{ $tag->id }})"
                                                @change="
                                                    if ($event.target.checked) {
                                                        selectedTask.tag_ids.push({{ $tag->id }})
                                                    } else {
                                                        selectedTask.tag_ids = selectedTask.tag_ids.filter(id => id !== {{ $tag->id }})
                                                    }
                                                    clearTimeout(saveTagTimer)
                                                    saveTagTimer = setTimeout(() => {
                                                        fetch('/tasks/' + selectedTask.id, {
                                                            method: 'PATCH',
                                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                                                            body: JSON.stringify({ tag_ids: selectedTask.tag_ids }),
                                                        })
                                                    }, 500)
                                                "
                                                class="rounded border-gray-300 text-brand-500">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded-full text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                {{-- Subtasks --}}
                                <div class="p-3">
                                    <h4 class="text-[10px] font-semibold text-gray-500 mb-2 uppercase tracking-wide">
                                        Subtasks
                                        <span x-show="selectedTask && selectedTask.subtasks && selectedTask.subtasks.length > 0" class="text-gray-400 font-normal"
                                            x-text="selectedTask ? '(' + selectedTask.subtasks.filter(s => s.is_completed).length + '/' + selectedTask.subtasks.length + ')' : ''"></span>
                                    </h4>
                                    <div class="space-y-1 mb-2">
                                        <template x-for="(sub, idx) in (selectedTask?.subtasks || [])" :key="sub.id">
                                            <div class="flex items-center gap-2 group">
                                                <form method="POST" x-bind:action="`/subtasks/${sub.id}/toggle`" class="inline" @click.stop>
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="w-3.5 h-3.5 rounded border flex-shrink-0 flex items-center justify-center transition"
                                                        x-bind:class="sub.is_completed ? 'bg-brand-500 border-brand-500' : 'border-gray-300 hover:border-brand-400'">
                                                        <svg x-show="sub.is_completed" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                                <span class="flex-1 text-[11px]" x-text="sub.title"
                                                    x-bind:class="sub.is_completed ? 'line-through text-gray-400' : 'text-gray-700'"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <form method="POST" x-bind:action="`/tasks/${selectedTask.id}/subtasks`" class="flex gap-1.5" @click.stop>
                                        @csrf
                                        <input type="text" name="title" required maxlength="255" placeholder="Add subtask..."
                                            class="flex-1 border border-gray-200 rounded px-2 py-1 text-[11px] outline-none focus:ring-2 focus:ring-brand-500">
                                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white text-[10px] font-medium px-2 py-1 rounded transition">Add</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="px-5 py-3 border-t border-gray-100"
                     x-data="{
                         html: '',
                         editor: null,
                         saveTimer: null,
                         save() {
                             clearTimeout(this.saveTimer);
                             this.saveTimer = setTimeout(() => {
                                 fetch('/tasks/' + selectedTask.id + '/description', {
                                     method: 'PATCH',
                                     headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                                     body: JSON.stringify({ description: this.html }),
                                 });
                             }, 500);
                         }
                     }"
                     x-effect="if (selectedTask && selectedTask.id) {
                         html = selectedTask.description || '';
                         if (editor && editor.getHTML() !== html) editor.commands.setContent(html, false);
                     }"
                     x-init="$watch('selectedTask.id', (id) => {
                         if (id && !editor) {
                             $nextTick(() => {
                                 editor = initTaskEditor($refs.pomoEditor, {
                                     content: selectedTask.description || '',
                                     placeholder: 'Start writing...',
                                     onUpdate: (val) => { html = val; this.save() },
                                 });
                             });
                         }
                     })">
                    <div x-ref="pomoEditor" class="task-editor-area min-h-[120px] text-sm leading-relaxed text-gray-800 outline-none"></div>
                </div>

                {{-- Comments --}}
                <div class="border-t border-gray-100">
                    <button @click="commentsOpen = !commentsOpen" class="w-full flex items-center gap-2 px-5 py-2.5 text-[11px] font-medium text-gray-500 hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Comments</span>
                        <span x-show="selectedTask && selectedTask.comments && selectedTask.comments.length > 0"
                            class="text-gray-400" x-text="selectedTask ? '(' + selectedTask.comments.length + ')' : ''"></span>
                    </button>
                    <div x-show="commentsOpen" x-cloak class="px-5 pb-4 space-y-2">
                        <template x-for="comment in (selectedTask?.comments || [])" :key="comment.id">
                            <div class="bg-gray-50 rounded-lg px-3 py-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-semibold text-gray-700" x-text="comment.user_name"></span>
                                    <span class="text-[9px] text-gray-400" x-text="comment.created_at"></span>
                                </div>
                                <p class="text-xs text-gray-600" x-text="comment.body"></p>
                            </div>
                        </template>
                        <form method="POST" x-bind:action="`/tasks/${selectedTask?.id}/comments`" class="flex gap-2" @click.stop>
                            @csrf
                            <input type="text" name="body" required maxlength="1000" placeholder="Add a comment..."
                                class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Post</button>
                        </form>
                    </div>
                </div>

            </div>
        </template>

        {{-- Focus Note --}}
        <div x-show="selectedTask">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Focus Note</label>
            <textarea x-model="focusNote" rows="2" placeholder="What do you want to focus on this session?"
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-white placeholder-gray-300"></textarea>
        </div>

        {{-- No task selected --}}
        <template x-if="!selectedTask">
            <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-400">Select a task to edit it here</p>
            </div>
        </template>

    </div>

</div>

<script>
function pomodoroApp() {
    return {
        mode: 'work',
        running: false,
        remaining: 25 * 60,
        total: 25 * 60,
        selectedTaskId: '{{ request("task_id", "") }}',
        selectedTask: null,
        pickerOpen: false,
        taskSearch: '',
        taskGroupBy: 'date',
        focusNote: '',
        allTasks: {{ Js::from($tasks->map(fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status->value,
            'priority' => $t->priority->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
            'project_id' => $t->project_id,
            'tag_ids' => $t->tags->pluck('id')->toArray(),
            'project' => $t->project ? ['id' => $t->project->id, 'name' => $t->project->name, 'color' => $t->project->color] : null,
            'tags' => $t->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name, 'color' => $tag->color])->values(),
            'subtasks' => $t->subtasks->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_completed' => $s->is_completed])->toArray(),
            'comments' => $t->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user->name, 'created_at' => $c->created_at->diffForHumans()])->toArray(),
        ])->toArray()) }},
        interval: null,
        stats: { work_count: 0, short_break_count: 0, long_break_count: 0, total_minutes: 0, sessions: [] },
        circumference: 2 * Math.PI * 90,

        get progress() { return this.circumference * (1 - this.remaining / this.total); },

        get timeDisplay() {
            const m = Math.floor(this.remaining / 60);
            const s = this.remaining % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        get modeLabel() {
            return this.mode === 'work' ? 'Focus' : this.mode === 'short_break' ? 'Short Break' : 'Long Break';
        },

        get filteredGroups() {
            const search = this.taskSearch.toLowerCase();
            const tasks = this.allTasks.filter(t => !search || t.title.toLowerCase().includes(search));
            if (this.taskGroupBy === 'date') return this.groupByDate(tasks);
            if (this.taskGroupBy === 'project') return this.groupByProject(tasks);
            if (this.taskGroupBy === 'tag') return this.groupByTag(tasks);
            return [];
        },

        groupByDate(tasks) {
            const today = new Date().toISOString().slice(0, 10);
            const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
            const weekEnd = new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10);
            const monthEnd = new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10);
            const groups = [
                { label: 'Today', tasks: [] }, { label: 'Tomorrow', tasks: [] },
                { label: 'This Week', tasks: [] }, { label: 'This Month', tasks: [] },
                { label: 'Later', tasks: [] }, { label: 'No Date', tasks: [] },
            ];
            tasks.forEach(t => {
                if (!t.due_date) { groups[5].tasks.push(t); return; }
                if (t.due_date === today) groups[0].tasks.push(t);
                else if (t.due_date === tomorrow) groups[1].tasks.push(t);
                else if (t.due_date <= weekEnd) groups[2].tasks.push(t);
                else if (t.due_date <= monthEnd) groups[3].tasks.push(t);
                else groups[4].tasks.push(t);
            });
            return groups.filter(g => g.tasks.length > 0);
        },

        groupByProject(tasks) {
            const map = {};
            tasks.forEach(t => {
                const name = t.project?.name || 'No Project';
                if (!map[name]) map[name] = { label: name, color: t.project?.color || '#9ca3af', tasks: [] };
                map[name].tasks.push(t);
            });
            return Object.values(map).sort((a, b) => a.label.localeCompare(b.label));
        },

        groupByTag(tasks) {
            const map = {};
            tasks.forEach(t => {
                if (t.tags.length === 0) {
                    if (!map['No Tag']) map['No Tag'] = { label: 'No Tag', color: '#9ca3af', tasks: [] };
                    map['No Tag'].tasks.push(t);
                } else {
                    t.tags.forEach(tag => {
                        if (!map[tag.name]) map[tag.name] = { label: tag.name, color: tag.color, tasks: [] };
                        map[tag.name].tasks.push(t);
                    });
                }
            });
            return Object.values(map).sort((a, b) => a.label.localeCompare(b.label));
        },

        selectTask(task) {
            this.selectedTaskId = task.id;
            this.selectedTask = JSON.parse(JSON.stringify(task));
        },

        durations: { work: 25 * 60, short_break: 5 * 60, long_break: 15 * 60 },

        saveState() {
            localStorage.setItem('pomodoro', JSON.stringify({
                mode: this.mode,
                remaining: this.remaining,
                total: this.total,
                running: this.running,
                selectedTaskId: this.selectedTaskId,
                focusNote: this.focusNote,
                savedAt: Date.now(),
            }));
        },

        loadState() {
            try {
                const raw = localStorage.getItem('pomodoro');
                if (!raw) return;
                const s = JSON.parse(raw);
                this.mode = s.mode || 'work';
                this.selectedTaskId = s.selectedTaskId || '';
                this.focusNote = s.focusNote || '';
                if (s.running) {
                    const elapsed = Math.floor((Date.now() - s.savedAt) / 1000);
                    this.remaining = Math.max(0, s.remaining - elapsed);
                    this.total = s.total;
                    if (this.remaining > 0) {
                        this.start();
                    } else {
                        this.complete();
                    }
                } else {
                    this.remaining = s.remaining ?? this.durations[this.mode];
                    this.total = s.total ?? this.durations[this.mode];
                }
            } catch(e) {}
        },

        init() {
            this.loadState();
            this.loadStats();
            if (this.selectedTaskId) {
                const t = this.allTasks.find(t => t.id == this.selectedTaskId);
                if (t) this.selectedTask = JSON.parse(JSON.stringify(t));
            }
            this.$watch('mode', () => this.saveState());
            this.$watch('remaining', () => this.saveState());
            this.$watch('selectedTaskId', () => this.saveState());
            this.$watch('focusNote', () => this.saveState());
            this.$watch('running', () => this.saveState());
        },

        switchMode(m) { this.pause(); this.mode = m; this.remaining = this.durations[m]; this.total = this.durations[m]; this.saveState(); },
        start() { this.running = true; this.interval = setInterval(() => { if (this.remaining > 0) this.remaining--; else this.complete(); }, 1000); },
        pause() { this.running = false; clearInterval(this.interval); this.saveState(); },
        reset() { this.pause(); this.remaining = this.durations[this.mode]; this.total = this.durations[this.mode]; this.saveState(); },
        skip() {
            this.pause();
            this.logSession(this.mode, this.durations[this.mode] - this.remaining);
            this.mode = this.mode === 'work' ? 'short_break' : 'work';
            this.remaining = this.durations[this.mode];
            this.total = this.durations[this.mode];
        },

        complete() {
            this.pause();
            const type = this.mode;
            this.logSession(type, this.durations[type]);
            if (type === 'work') {
                const workCount = (this.stats.work_count || 0) + 1;
                this.mode = workCount % 4 === 0 ? 'long_break' : 'short_break';
            } else {
                this.mode = 'work';
            }
            this.remaining = this.durations[this.mode];
            this.total = this.durations[this.mode];
            this.loadStats();
            this.notify(type);
        },

        logSession(type, duration) {
            fetch('/pomodoro/sessions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ task_id: this.selectedTaskId || null, note: this.focusNote || null, type, duration }),
            });
        },

        loadStats() {
            fetch('/pomodoro/stats', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json()).then(data => { this.stats = data; });
        },

        notify(type) {
            const labels = { work: 'Focus session complete!', short_break: 'Break is over!', long_break: 'Long break is over!' };
            if ('Notification' in window && Notification.permission === 'granted') new Notification(labels[type] || 'Timer complete');
        },
    };
}
</script>
</x-app-layout>
