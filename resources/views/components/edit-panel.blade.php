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
            :class="editTask.status === 'done' ? 'bg-sky-500 border-sky-500' : 'border-gray-300 hover:border-sky-400'">
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

        {{-- Comments --}}
        <div x-show="commentsOpen" x-cloak x-transition class="px-6 py-4 border-t border-gray-200">
            <div class="space-y-3 mb-3">
                <template x-for="comment in editTask.comments" :key="comment.id">
                    <div class="flex gap-2">
                        <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-500 flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5"
                            x-text="comment.user_name.charAt(0).toUpperCase()">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-800" x-text="comment.body"></p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-400" x-text="comment.user_name"></span>
                                <span class="text-[10px] text-gray-300">&middot;</span>
                                <span class="text-[10px] text-gray-400" x-text="comment.created_at"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <form method="POST" x-bind:action="`/tasks/${editTask.id}/comments`" class="flex gap-2">
                @csrf
                <textarea name="body" required maxlength="1000" placeholder="Write a comment..."
                    class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent resize-none"
                    rows="2"></textarea>
                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition self-end">Send</button>
            </form>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="flex items-center justify-end gap-1 px-4 py-2.5 border-t border-gray-200 flex-shrink-0">
        <button @click="commentsOpen = !commentsOpen"
            class="p-2 rounded-lg transition"
            :class="commentsOpen ? 'bg-sky-50 text-sky-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>
        <div class="relative">
            <button @click="menuOpen = !menuOpen"
                class="p-2 rounded-lg transition"
                :class="menuOpen ? 'bg-sky-50 text-sky-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'">
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
                                    if ($event.target.checked) { editTask.tag_ids.push({{ $tag->id }}) } else { editTask.tag_ids = editTask.tag_ids.filter(id => id !== {{ $tag->id }}) }
                                    clearTimeout(saveTagTimer)
                                    saveTagTimer = setTimeout(() => {
                                        fetch('/tasks/' + editTask.id, {
                                            method: 'PATCH',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                                            body: JSON.stringify({ tag_ids: editTask.tag_ids }),
                                        })
                                    }, 500)
                                "
                                class="rounded border-gray-300 text-sky-500">
                            <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
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
                            x-text="`(${editTask.subtasks.filter(s => s.is_completed).length}/${editTask.subtasks.length})`"></span>
                    </h4>
                    <div class="space-y-1.5 mb-2">
                        <template x-for="(subtask, index) in editTask.subtasks" :key="subtask.id">
                            <div class="flex items-center gap-2 group">
                                <form method="POST" x-bind:action="`/subtasks/${subtask.id}/toggle`" class="inline" @click.stop>
                                    @csrf @method('PATCH')
                                    <button type="submit" class="w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition"
                                        x-bind:class="subtask.is_completed ? 'bg-sky-500 border-sky-500' : 'border-gray-300 hover:border-sky-400'">
                                        <svg x-show="subtask.is_completed" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                                <span class="flex-1 text-xs" x-text="subtask.title"
                                    x-bind:class="subtask.is_completed ? 'line-through text-gray-400' : 'text-gray-700'"></span>
                                <form method="POST" x-bind:action="`/subtasks/${subtask.id}`" class="inline opacity-0 group-hover:opacity-100 transition" @click.stop>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-0.5 text-gray-300 hover:text-red-500 transition" title="Delete">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </template>
                    </div>
                    <form method="POST" x-bind:action="`/tasks/${editTask.id}/subtasks`" class="flex gap-2" @click.stop>
                        @csrf
                        <input type="text" name="title" required maxlength="255" placeholder="Add subtask..."
                            class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition whitespace-nowrap">Add</button>
                    </form>
                </div>

                {{-- Start Focus --}}
                <div class="p-3 border-t border-gray-100">
                    <a :href="'/pomodoro?task_id=' + editTask.id" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-sky-500 hover:bg-sky-50 transition" @click.stop>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Start Focus Session
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
