<x-app-layout>
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div x-data="{
        editOpen: false,
        editTask: { id: null, title: '', description: '', priority: 'medium', status: 'todo', due_date: '', is_recurring: false, tag_ids: [], project_id: '', subtasks: [], comments: [] },
        newOpen: false,
        tasks: {{ Js::from($tasks->map(fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'priority' => $t->priority->value,
            'status' => $t->status->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
            'is_recurring' => $t->is_recurring,
            'project_name' => $t->project?->name ?? 'No project',
            'tag_ids' => $t->tags->pluck('id')->toArray(),
            'project_id' => $t->project_id,
            'subtasks' => $t->subtasks->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_completed' => $s->is_completed])->toArray(),
            'comments' => $t->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user->name, 'created_at' => $c->created_at->diffForHumans()])->toArray(),
        ])->toArray()) }},
        openEdit(task) {
            this.editTask = JSON.parse(JSON.stringify(task));
            this.editOpen = true;
        },
        init() {
            const params = new URLSearchParams(window.location.search);
            const editId = params.get('edit');
            if (editId) {
                const task = this.tasks.find(t => t.id == editId);
                if (task) this.openEdit(task);
                history.replaceState(null, '', window.location.pathname);
            }
        },
        toggleTaskStatus(task) {
            task.status = task.status === 'done' ? 'todo' : 'done';
            fetch('/tasks/' + task.id + '/toggle', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
            });
        },
        deleteTask(index) {
            if (!confirm('Are you sure you want to delete this task?')) return;
            const task = this.tasks[index];
            fetch('/tasks/' + task.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
            }).then(() => this.tasks.splice(index, 1));
        },
        isToday(dateStr) {
            if (!dateStr) return false;
            const d = new Date(dateStr + 'T00:00:00');
            const now = new Date();
            return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate();
        },
        isOverdue(dateStr, status) {
            if (!dateStr || status === 'done') return false;
            const d = new Date(dateStr + 'T23:59:59');
            return d < new Date();
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },
        syncTask() {
            const idx = this.tasks.findIndex(t => t.id === this.editTask.id);
            if (idx !== -1) this.tasks[idx] = JSON.parse(JSON.stringify(this.editTask));
        }
    }"
         x-on:open-task-panel.document="newOpen = true">
    <div class="flex gap-6">

        {{-- Left Column --}}
        <div class="flex-1 min-w-0">

            {{-- Greeting --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                    {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ now()->format('l, F j') }} ·
                    @if($tasksDueToday > 0)
                        You have {{ $tasksDueToday }} task{{ $tasksDueToday > 1 ? 's' : '' }} due today
                    @else
                        No tasks due today 🎉
                    @endif
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">

                {{-- Total --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Total Tasks</span>
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalTasks }}</p>
                    <p class="text-xs text-gray-400 mt-1">+3 this week</p>
                </div>

                {{-- Completed --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Completed</span>
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $completedTasks }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% completion rate
                    </p>
                </div>

                {{-- Overdue --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Overdue</span>
                        <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold {{ $overdueTasks > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $overdueTasks }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $overdueTasks > 0 ? $overdueTasks . ' high priority' : 'All caught up!' }}
                    </p>
                </div>

            </div>

            {{-- My Tasks --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">My Tasks</h2>
                    <div class="flex items-center gap-2">
                        <button class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Filter</button>
                        <button class="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Sort</button>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    <template x-for="(task, index) in tasks" :key="task.id">
                        <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 transition group cursor-pointer"
                             @click="openEdit(task)">

                            {{-- Checkbox --}}
                            <button @click.stop="toggleTaskStatus(task)"
                                class="w-[18px] h-[18px] rounded-[5px] border-[1.5px] flex-shrink-0 flex items-center justify-center cursor-pointer transition"
                                :class="task.status === 'done' ? 'bg-violet-600 border-violet-600' : 'border-gray-300 hover:border-violet-400'">
                                <svg x-show="task.status === 'done'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            {{-- Title --}}
                            <span class="flex-1 min-w-0 text-sm truncate transition"
                                  :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'"
                                  x-text="task.title"></span>

                            {{-- Subtask count --}}
                            <span x-show="task.subtasks.length > 0"
                                  class="text-[10px] text-gray-400 flex-shrink-0 tabular-nums"
                                  x-text="task.subtasks.filter(s => s.is_completed).length + '/' + task.subtasks.length"></span>

                            {{-- Delete --}}
                            <button @click.stop="deleteTask(index)"
                                class="p-1 text-gray-300 hover:text-red-500 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                                title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                        </div>
                    </template>

                    <div x-show="tasks.length === 0" class="px-5 py-10 text-center text-gray-400 text-sm">
                        No tasks yet — create your first one!
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Calendar --}}
        <div class="w-72 flex-shrink-0 space-y-4">

            {{-- Calendar --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">{{ now()->format('F Y') }}</h3>
                    <div class="flex items-center gap-1">
                        <button class="p-1 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="p-1 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Day headers --}}
                <div class="grid grid-cols-7 mb-2">
                    @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $day)
                    <div class="text-center text-xs text-gray-400 py-1">{{ $day }}</div>
                    @endforeach
                </div>

                {{-- Calendar days --}}
                @php
                    $startOfMonth = now()->startOfMonth();
                    $daysInMonth = now()->daysInMonth;
                    $startDow = $startOfMonth->dayOfWeekIso - 1;
                @endphp
                <div class="grid grid-cols-7 gap-y-1">
                    @for($i = 0; $i < $startDow; $i++)
                        <div></div>
                    @endfor
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php $dayDate = now()->startOfMonth()->addDays($day - 1)->format('Y-m-d'); @endphp
                        <div class="flex items-center justify-center">
                            <a href="{{ route('tasks.index', ['date' => $dayDate]) }}"
                                class="w-7 h-7 flex items-center justify-center text-xs rounded-full
                                {{ $day === now()->day ? 'bg-violet-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                {{ $day }}
                            </a>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Upcoming --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Upcoming</h3>
                <div class="space-y-2">
                    @forelse($tasks->whereNotNull('due_date')->where('due_date', '>=', now()->toDateString())->sortBy('due_date')->take(4) as $task)
                    <div class="flex items-start gap-2">
                        <div class="w-1 h-8 rounded-full bg-violet-500 flex-shrink-0 mt-0.5"></div>
                        <div>
                            <p class="text-xs font-medium text-gray-800">{{ $task->title }}</p>
                            <p class="text-xs text-gray-400">{{ $task->due_date->format('M j') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No upcoming tasks</p>
                    @endforelse
                </div>
            </div>

        </div>
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
            x-transition:leave-end="translate-x-full"
            x-data="{ menuOpen: false, commentsOpen: false, saveTagTimer: null }">

            <div class="flex items-center gap-3 px-6 py-3 border-b border-gray-200 flex-shrink-0">
                <button @click="
                    editTask.status = editTask.status === 'done' ? 'todo' : 'done';
                    fetch('/tasks/' + editTask.id, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ status: editTask.status }),
                    });
                    syncTask();
                " class="w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center transition"
                    :class="editTask.status === 'done' ? 'bg-violet-600 border-violet-600' : 'border-gray-300 hover:border-violet-400'">
                    <svg x-show="editTask.status === 'done'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <div class="w-px h-4 bg-gray-200"></div>
                    <div class="flex items-center gap-1.5"
                         @date-picker-ok.window="if (editOpen) { editTask.due_date = $event.detail.date || ''; syncTask(); }">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <x-date-picker />
                </div>
                <div class="flex-1"></div>
                <div class="relative" x-data="{ flagOpen: false }" @click.outside="flagOpen = false">
                    <button @click="flagOpen = !flagOpen" class="p-1.5 rounded-lg transition hover:bg-gray-50">
                        <svg class="w-4 h-4" :class="{
                            'text-green-500': editTask.priority === 'low',
                            'text-yellow-500': editTask.priority === 'medium',
                            'text-red-500': editTask.priority === 'high',
                            'text-gray-300': !editTask.priority
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
                        <button @click="editTask.priority = 'low'; flagOpen = false; fetch('/tasks/' + editTask.id, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ priority: 'low' }) }); syncTask();"
                            class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                            :class="editTask.priority === 'low' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            Low
                        </button>
                        <button @click="editTask.priority = 'medium'; flagOpen = false; fetch('/tasks/' + editTask.id, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ priority: 'medium' }) }); syncTask();"
                            class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                            :class="editTask.priority === 'medium' ? 'bg-yellow-50 text-yellow-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-4 h-4 text-yellow-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            Medium
                        </button>
                        <button @click="editTask.priority = 'high'; flagOpen = false; fetch('/tasks/' + editTask.id, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ priority: 'high' }) }); syncTask();"
                            class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition"
                            :class="editTask.priority === 'high' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600'">
                            <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                            High
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto min-h-0">
                <x-task-form alpine x-bind:action="`/tasks/${editTask.id}`" />

                {{-- Description editor --}}
                <div class="px-6 py-4 border-t border-gray-200"
                     x-data="{
                         html: '',
                         editor: null,
                         saveTimer: null,
                         save() {
                             clearTimeout(this.saveTimer)
                             this.saveTimer = setTimeout(() => {
                                 fetch('/tasks/' + editTask.id + '/description', {
                                     method: 'PATCH',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                         'X-Requested-With': 'XMLHttpRequest',
                                     },
                                     body: JSON.stringify({ description: this.html }),
                                 })
                             }, 500)
                         }
                     }"
                     x-effect="if (editOpen && editTask.id) {
                         html = editTask.description || '';
                         if (editor && editor.getHTML() !== html) {
                             editor.commands.setContent(html, false);
                         }
                     }"
                     x-init="$watch('editOpen', (open) => {
                         if (open && editTask.id) {
                             if (editor) editor.destroy();
                             $nextTick(() => {
                                 editor = initTaskEditor($refs.descEditor, {
                                     content: editTask.description || '',
                                     placeholder: 'Start writing...',
                                     onUpdate: (val) => { html = val; this.save() },
                                 })
                             })
                         } else if (!open && editor) {
                             editor.destroy();
                             editor = null;
                         }
                     })">
                    <div x-ref="descEditor" class="task-editor-area min-h-[200px] text-sm leading-relaxed text-gray-800 outline-none"></div>
                </div>

                {{-- Comments section (toggled) --}}
                <div x-show="commentsOpen" x-cloak x-transition class="px-6 py-4 border-t border-gray-200">
                    <div class="space-y-3 mb-3">
                        <template x-for="comment in editTask.comments" :key="comment.id">
                            <div class="flex gap-2">
                                <div class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5"
                                    x-text="comment.user_name.charAt(0).toUpperCase()">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-800" x-text="comment.body"></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] text-gray-400" x-text="comment.user_name"></span>
                                        <span class="text-[10px] text-gray-300">·</span>
                                        <span class="text-[10px] text-gray-400" x-text="comment.created_at"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <form method="POST" x-bind:action="`/tasks/${editTask.id}/comments`" class="flex gap-2">
                        @csrf
                        <textarea name="body" required maxlength="1000" placeholder="Write a comment..."
                            class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none"
                            rows="2"></textarea>
                        <button type="submit"
                            class="bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition self-end">
                            Send
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="flex items-center justify-end gap-1 px-4 py-2.5 border-t border-gray-200 flex-shrink-0">
                {{-- Comments icon --}}
                <button @click="commentsOpen = !commentsOpen"
                    class="p-2 rounded-lg transition"
                    :class="commentsOpen ? 'bg-violet-50 text-violet-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </button>

                {{-- Three dots menu --}}
                <div class="relative">
                    <button @click="menuOpen = !menuOpen"
                        class="p-2 rounded-lg transition"
                        :class="menuOpen ? 'bg-violet-50 text-violet-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.5"/>
                            <circle cx="12" cy="12" r="1.5"/>
                            <circle cx="12" cy="19" r="1.5"/>
                        </svg>
                    </button>

                    <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute bottom-full right-0 mb-2 w-72 bg-white border border-gray-200 rounded-xl shadow-lg z-10 max-h-80 overflow-y-auto">

                        {{-- Tags --}}
                        <div class="p-3 border-b border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Tags</h4>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($tags as $tag)
                                <label class="flex items-center gap-1.5 cursor-pointer" @click.stop>
                                    <input type="checkbox" value="{{ $tag->id }}"
                                        :checked="editTask.tag_ids.includes({{ $tag->id }})"
                                        @change="
                                            if ($event.target.checked) {
                                                editTask.tag_ids.push({{ $tag->id }})
                                            } else {
                                                editTask.tag_ids = editTask.tag_ids.filter(id => id !== {{ $tag->id }})
                                            }
                                            clearTimeout(saveTagTimer)
                                            saveTagTimer = setTimeout(() => {
                                                fetch('/tasks/' + editTask.id, {
                                                    method: 'PATCH',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                        'X-Requested-With': 'XMLHttpRequest',
                                                    },
                                                    body: JSON.stringify({ tag_ids: editTask.tag_ids }),
                                                })
                                            }, 500)
                                        "
                                        class="rounded border-gray-300 text-violet-600">
                                    <span class="text-xs px-2 py-0.5 rounded-full text-white"
                                        style="background-color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                    </span>
                                </label>
                                @endforeach
                                @if(count($tags) === 0)
                                <p class="text-xs text-gray-400">No tags yet</p>
                                @endif
                            </div>
                        </div>

                        {{-- Subtasks --}}
                        <div class="p-3">
                            <h4 class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">
                                Subtasks
                                <span x-show="editTask.subtasks.length > 0" class="text-gray-400 font-normal"
                                    x-text="`(${editTask.subtasks.filter(s => s.is_completed).length}/${editTask.subtasks.length})`">
                                </span>
                            </h4>

                            <div class="space-y-1.5 mb-2">
                                <template x-for="(subtask, index) in editTask.subtasks" :key="subtask.id">
                                    <div class="flex items-center gap-2 group">
                                        <form method="POST" x-bind:action="`/subtasks/${subtask.id}/toggle`" class="inline" @click.stop>
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition"
                                                x-bind:class="subtask.is_completed ? 'bg-violet-600 border-violet-600' : 'border-gray-300 hover:border-violet-400'">
                                                <svg x-show="subtask.is_completed" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <span class="flex-1 text-xs" x-text="subtask.title"
                                            x-bind:class="subtask.is_completed ? 'line-through text-gray-400' : 'text-gray-700'">
                                        </span>
                                        <form method="POST" x-bind:action="`/subtasks/${subtask.id}`" class="inline opacity-0 group-hover:opacity-100 transition" @click.stop>
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-0.5 text-gray-300 hover:text-red-500 transition" title="Delete">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>

                            <form method="POST" x-bind:action="`/tasks/${editTask.id}/subtasks`" class="flex gap-2" @click.stop>
                                @csrf
                                <input type="text" name="title" required maxlength="255" placeholder="Add subtask..."
                                    class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                <button type="submit"
                                    class="bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    Add
                                </button>
                            </form>
                        </div>
                    </div>
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

        <div x-show="newOpen" x-cloak
            class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-data="{ newTitle: '', newPriority: 'medium', newDate: '' }">

            <div class="grid grid-cols-3 gap-2 px-6 py-3 border-b border-gray-200 flex-shrink-0 items-center">
                <div class="flex items-center justify-center">
                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center">
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <input type="date" x-model="newDate"
                        class="w-full text-center text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-violet-500 text-gray-700">
                </div>
                <div class="flex items-center justify-center">
                    <select x-model="newPriority"
                        class="w-full text-center text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-violet-500 text-gray-700">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <x-task-form :tags="$tags" :projects="$projects" />
        </div>

    </div>

</x-app-layout>
