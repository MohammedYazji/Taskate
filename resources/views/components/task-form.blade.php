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
                class="w-full text-lg font-semibold text-gray-900 outline-none border-0 bg-transparent placeholder-gray-300"
                placeholder="Task title...">
        </div>

        @if(!$alpine)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select name="priority"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500">
                <option value="low" {{ isset($task) && $task->priority->value === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ !isset($task) || $task->priority->value === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ isset($task) && $task->priority->value === 'high' ? 'selected' : '' }}>High</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date" name="due_date"
                value="{{ $task && $task->due_date ? $task->due_date->format('Y-m-d') : ($defaultDate ?? '') }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500">
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_recurring" value="0">
                <input type="checkbox" name="is_recurring" value="1"
                    {{ $task && $task->is_recurring ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <span class="text-sm text-gray-700">Recurring task</span>
            </label>
        </div>
        @endif
    </div>

    @if($alpine)
    </div>
    @endif

    @if(!$alpine)
    <div class="px-6 py-4 border-t border-gray-200">
        <button type="submit"
            class="w-full bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
            Create Task
        </button>
    </div>
    @endif
</form>
