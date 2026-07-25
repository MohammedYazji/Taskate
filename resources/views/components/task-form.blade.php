@props(['task' => null, 'alpine' => false, 'defaultDate' => null, 'tags' => [], 'projects' => []])

<form
    @if(!$alpine)
        action="{{ $task ? route('tasks.update', $task) : route('tasks.store') }}"
    @endif
    method="POST"
    {{ $attributes->merge(['class' => 'flex flex-col flex-1 overflow-y-auto']) }}>
    @csrf
    @if($alpine || $task) @method('PUT') @endif

    @if($alpine)
    <div x-data="{
        saveTimer: null,
        saveField() {
            clearTimeout(this.saveTimer)
            this.saveTimer = setTimeout(() => {
                fetch('/tasks/' + editTask.id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title: editTask.title,
                        priority: editTask.priority,
                        due_date: editTask.due_date || null,
                        is_recurring: editTask.is_recurring,
                    }),
                })
                if (typeof syncTask === 'function') syncTask();
            }, 500)
        }
    }">
    @endif

    <div class="px-6 py-5 space-y-5 flex-1">
        <div>
            <input type="text" name="title" required
                @if(!$alpine) value="{{ $task->title ?? '' }}" @endif
                @if($alpine) x-model="editTask.title" @input="saveField()" @endif
                class="w-full text-lg font-semibold text-gray-900 outline-none ring-0 border-0 bg-transparent placeholder-gray-300"
                placeholder="Task title...">
        </div>

        @if(!$alpine)
        @if(count($projects) === 1)
        <input type="hidden" name="project_id" value="{{ $projects[0]->id }}">
        @endif
        <input type="hidden" name="priority" x-model="newPriority">
        <input type="hidden" name="due_date" x-model="newDate">
        <input type="hidden" name="is_recurring" value="0">
        <input type="hidden" name="section_id" x-model="newSectionId">
        @endif
    </div>

    @if($alpine)
    </div>
    @endif

    @if(!$alpine)
    <div class="px-6 py-4 border-t border-gray-200">
        <button type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 rounded-lg transition">
            Create Task
        </button>
    </div>
    @endif
</form>
