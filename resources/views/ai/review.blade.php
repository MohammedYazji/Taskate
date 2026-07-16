<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('ai.form') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Review AI Tasks</h1>
            </div>
            <p class="text-sm text-gray-500">Review, edit, and approve tasks for: <span class="font-medium text-gray-700">{{ $topic }}</span></p>
        </div>

        <form method="POST" action="{{ route('ai.approve') }}">
            @csrf

            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                <input type="text" name="project_name" required value="{{ $projectName }}"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                    placeholder="Enter project name...">
                <p class="text-xs text-gray-400 mt-1">All approved tasks will be added to this project</p>
            </div>

            <div class="space-y-4 mb-6">
                @foreach($tasks as $index => $task)
                <div class="bg-white rounded-xl border border-gray-200 hover:border-violet-200 transition"
                     x-data="{ editing: false }">

                    {{-- View Mode --}}
                    <div x-show="!editing" class="p-5">
                        <div class="flex items-start gap-4">
                            <input type="checkbox"
                                name="tasks[{{ $index }}][approved]"
                                value="1"
                                checked
                                class="mt-1 w-5 h-5 rounded border-gray-300 text-violet-600 focus:ring-violet-500">

                            <div class="flex-1 min-w-0">
                                <input type="hidden" name="tasks[{{ $index }}][title]" :value="$el.closest('[x-data]').querySelector('input[name=title]').value || '{{ $task['title'] }}'">
                                <input type="hidden" name="tasks[{{ $index }}][description]" :value="$el.closest('[x-data]').querySelector('textarea[name=description]').value || '{{ $task['description'] ?? '' }}'">
                                <input type="hidden" name="tasks[{{ $index }}][priority]" :value="$el.closest('[x-data]').querySelector('select[name=priority]').value || '{{ $task['priority'] }}'">

                                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $task['title'] }}</h3>
                                @if(!empty($task['description']))
                                <p class="text-xs text-gray-500 mb-3">{{ $task['description'] }}</p>
                                @endif

                                <div class="flex items-center gap-2">
                                    @php
                                        $colors = [
                                            'high' => 'text-red-600 bg-red-50 border-red-100',
                                            'medium' => 'text-orange-500 bg-orange-50 border-orange-100',
                                            'low' => 'text-green-600 bg-green-50 border-green-100',
                                        ];
                                        $color = $colors[$task['priority']] ?? 'text-gray-500 bg-gray-100';
                                    @endphp
                                    <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $color }}">
                                        {{ ucfirst($task['priority']) }}
                                    </span>
                                    <span class="text-xs text-gray-400">Status: To Do</span>
                                </div>
                            </div>

                            <button type="button" @click="editing = true"
                                class="p-1.5 text-gray-300 hover:text-violet-500 hover:bg-violet-50 rounded-lg transition"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Edit Mode --}}
                    <div x-show="editing" x-cloak class="p-5 space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-violet-600">Editing Task</span>
                            <button type="button" @click="editing = false"
                                class="text-xs text-gray-400 hover:text-gray-600 transition">
                                Done
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" value="{{ $task['title'] }}" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none">{{ $task['description'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-500">
                                <option value="low" {{ $task['priority'] === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $task['priority'] === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $task['priority'] === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium py-3 rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve Selected Tasks
                </button>

                <a href="{{ route('ai.form') }}"
                    class="px-6 py-3 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
