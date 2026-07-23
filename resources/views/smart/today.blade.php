<x-app-layout>
    <div x-data="{
        editOpen: false,
        editTask: { id: null, title: '', description: '', priority: 'medium', status: 'todo', due_date: '', is_recurring: false, tag_ids: [], project_id: '', subtasks: [], comments: [] },
        tasks: {{ Js::from($tasks->map(fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'priority' => $t->priority->value,
            'status' => $t->status->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
            'is_recurring' => $t->is_recurring,
            'tag_ids' => $t->tags->pluck('id')->toArray(),
            'project_id' => $t->project_id,
            'subtasks' => $t->subtasks->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_completed' => $s->is_completed])->toArray(),
            'comments' => $t->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user->name, 'created_at' => $c->created_at->diffForHumans()])->toArray(),
        ])->toArray()) }},
        openEdit(task) {
            this.editTask = JSON.parse(JSON.stringify(task));
            this.editOpen = true;
        },
        toggleTaskStatus(task) {
            task.status = task.status === 'done' ? 'todo' : 'done';
            fetch('/tasks/' + task.id + '/toggle', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
            });
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },
        isOverdue(dateStr, status) {
            if (!dateStr || status === 'done') return false;
            return new Date(dateStr + 'T23:59:59') < new Date();
        },
        syncTask() {
            const idx = this.tasks.findIndex(t => t.id === this.editTask.id);
            if (idx !== -1) this.tasks[idx] = JSON.parse(JSON.stringify(this.editTask));
        }
    }">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Today</h1>
        <p class="text-sm text-gray-500 mb-6">{{ now()->format('l, F j') }}</p>

        <div class="space-y-1">
            <template x-for="(task, index) in tasks" :key="task.id">
                <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition cursor-pointer group" @click="openEdit(task)">
                    <button @click.stop="toggleTaskStatus(task)" class="w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center transition"
                        :class="task.status === 'done' ? 'bg-sky-500 border-sky-500' : 'border-gray-300 hover:border-sky-400'">
                        <svg x-show="task.status === 'done'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                    <span class="flex-1 text-sm" :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-900'" x-text="task.title"></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold uppercase"
                        :class="{'text-red-500 bg-red-50': task.priority === 'high', 'text-orange-500 bg-orange-50': task.priority === 'medium', 'text-green-600 bg-green-50': task.priority === 'low'}"
                        x-text="task.priority"></span>
                </div>
            </template>
            <div x-show="tasks.length === 0" class="text-center text-gray-400 py-12">No tasks due today</div>
        </div>

        @include('components.edit-panel')
    </div>
</x-app-layout>
