@props(['task' => null, 'alpine' => false])

<form
    @if(!$alpine)
        action="{{ $task ? route('tasks.update', $task) : route('tasks.store') }}"
    @endif
    method="POST"
    {{ $attributes->merge(['class' => 'flex flex-col flex-1 overflow-y-auto']) }}>
    @csrf
    @if($alpine || $task) @method('PUT') @endif

    <div class="px-6 py-5 space-y-5 flex-1">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" required
                @if(!$alpine) value="{{ $task->title ?? '' }}" @endif
                @if($alpine) x-model="editTask.title" @endif
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                placeholder="Task title...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            @if($alpine)
                <textarea name="description" rows="3" x-model="editTask.description"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none"
                    placeholder="Add more details..."></textarea>
            @else
                <textarea name="description" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none"
                    placeholder="Add more details...">{{ $task->description ?? '' }}</textarea>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select name="priority"
                @if($alpine) x-model="editTask.priority" @endif
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500">
                <option value="low" {{ !$alpine && isset($task) && $task->priority->value === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ !$alpine && (!isset($task) || $task->priority->value === 'medium') ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ !$alpine && isset($task) && $task->priority->value === 'high' ? 'selected' : '' }}>High</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date" name="due_date"
                @if(!$alpine) value="{{ $task && $task->due_date ? $task->due_date->format('Y-m-d') : '' }}" @endif
                @if($alpine) x-model="editTask.due_date" @endif
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500">
        </div>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        <button type="submit"
            class="w-full bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
            {{ $alpine ? 'Save Changes' : ($task ? 'Save Changes' : 'Create Task') }}
        </button>
    </div>
</form>
