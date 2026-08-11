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
        syncTask() {
            const idx = this.tasks.findIndex(t => t.id === this.editTask.id);
            if (idx !== -1) this.tasks[idx] = JSON.parse(JSON.stringify(this.editTask));
        }
    }">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Completed</h1>
                <p class="text-sm text-gray-500" x-text="tasks.length + ' task' + (tasks.length !== 1 ? 's' : '') + ' completed'"></p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-2 mb-5">
            {{-- Date filter --}}
            <div class="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden text-xs font-medium">
                <a href="?date=all&project={{ $projectFilter }}" class="px-3 py-1.5 transition {{ $dateFilter === 'all' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">All dates</a>
                <a href="?date=week&project={{ $projectFilter }}" class="px-3 py-1.5 transition border-l border-gray-200 {{ $dateFilter === 'week' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">This week</a>
                <a href="?date=last_week&project={{ $projectFilter }}" class="px-3 py-1.5 transition border-l border-gray-200 {{ $dateFilter === 'last_week' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">Last week</a>
                <a href="?date=month&project={{ $projectFilter }}" class="px-3 py-1.5 transition border-l border-gray-200 {{ $dateFilter === 'month' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">This month</a>
            </div>

            {{-- Project filter --}}
            <div class="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden text-xs font-medium">
                <a href="?date={{ $dateFilter }}&project=all" class="px-3 py-1.5 transition {{ $projectFilter === 'all' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">All lists</a>
                <a href="?date={{ $dateFilter }}&project=inbox" class="px-3 py-1.5 transition border-l border-gray-200 {{ $projectFilter === 'inbox' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">Inbox</a>
                @foreach($projects as $p)
                    @if($p->name !== 'Inbox')
                    <a href="?date={{ $dateFilter }}&project={{ $p->id }}" class="px-3 py-1.5 transition border-l border-gray-200 max-w-[120px] truncate {{ $projectFilter === (string) $p->id ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">{{ $p->icon }} {{ $p->name }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Task list --}}
        <div class="space-y-1">
            <template x-for="(task, index) in tasks" :key="task.id">
                <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition cursor-pointer group" @click="openEdit(task)">
                    <button @click.stop="toggleTaskStatus(task)" class="w-5 h-5 rounded-[5px] border-2 flex-shrink-0 flex items-center justify-center transition"
                        :class="task.status === 'done' ? 'border-transparent' : task.status === 'wont_do' ? 'border-transparent' : 'border-gray-300'"
                        :style="task.status === 'done' ? 'background-color: #14B8A6' : task.status === 'wont_do' ? 'background-color: #9CA3AF' : ''">
                        <svg x-show="task.status === 'done'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="task.status === 'wont_do'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                    <span class="flex-1 text-sm line-through text-gray-400" x-text="task.title"></span>
                    <span x-show="task.status === 'wont_do'" class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Won't do</span>
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded"
                        :class="{'text-red-500 bg-red-50': task.priority === 'high', 'text-orange-500 bg-orange-50': task.priority === 'medium', 'text-green-600 bg-green-50': task.priority === 'low'}"
                        x-text="task.priority"></span>
                </div>
            </template>
            <div x-show="tasks.length === 0" class="text-center text-gray-400 py-12">No completed tasks found</div>
        </div>

        @include('components.edit-panel')
    </div>
</x-app-layout>
