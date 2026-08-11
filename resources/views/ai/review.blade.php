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

            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                    <input type="text" name="project_name" required value="{{ $projectName }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="Enter project name...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Folder <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="folder_id"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent bg-white">
                        <option value="">No folder</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ ($folderId ?? null) == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Sections with tasks --}}
            @foreach($sections as $si => $section)
            <div class="mb-4" x-data="{ sectionApproved: true }">
                {{-- Section header --}}
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" x-model="sectionApproved"
                        class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    <input type="text" name="sections[{{ $si }}][name]" value="{{ $section['name'] }}" required
                        class="text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg px-2 py-1 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <input type="hidden" name="sections[{{ $si }}][approved]" :value="sectionApproved ? '1' : '0'">
                </div>

                {{-- Tasks in section --}}
                <div class="space-y-2 ml-7">
                    @foreach($section['tasks'] as $ti => $task)
                    <div class="bg-white rounded-xl border border-gray-200 hover:border-brand-400/30 transition p-4"
                         x-data="{
                             editing: false,
                             fTitle: @js($task['title']),
                             fDesc: @js($task['description'] ?? ''),
                             fPriority: @js($task['priority']),
                             fDueDate: @js($task['due_date'] ?? ''),
                             subtasks: {{ json_encode(!empty($task['subtasks']) ? $task['subtasks'] : []) }},
                             addSubtask() {
                                 this.subtasks.push({title: '', approved: 1});
                                 this.$nextTick(() => {
                                     const inputs = this.$el.querySelectorAll('.subtask-input');
                                     inputs[inputs.length - 1]?.focus();
                                 });
                             },
                             removeSubtask(idx) { this.subtasks.splice(idx, 1); }
                         }">
                        {{-- Hidden inputs (always submit from Alpine data) --}}
                        <input type="hidden" name="sections[{{ $si }}][tasks][{{ $ti }}][title]" :value="fTitle">
                        <input type="hidden" name="sections[{{ $si }}][tasks][{{ $ti }}][description]" :value="fDesc">
                        <input type="hidden" name="sections[{{ $si }}][tasks][{{ $ti }}][priority]" :value="fPriority">
                        <input type="hidden" name="sections[{{ $si }}][tasks][{{ $ti }}][due_date]" :value="fDueDate">
                        <template x-for="(sub, si2) in subtasks" :key="'h-'+si2">
                            <div>
                                <input type="hidden" :name="'sections[{{ $si }}][tasks][{{ $ti }}][subtasks]['+si2+'][title]'" :value="sub.title">
                                <input type="hidden" :name="'sections[{{ $si }}][tasks][{{ $ti }}][subtasks]['+si2+'][approved]'" :value="sub.approved">
                            </div>
                        </template>

                        <div x-show="!editing" class="flex items-start gap-3">
                            <input type="checkbox" name="sections[{{ $si }}][tasks][{{ $ti }}][approved]" value="1" checked
                                class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900" x-text="fTitle"></h3>
                                <template x-if="fDesc">
                                    <pre class="text-xs text-gray-500 mt-1 whitespace-pre-wrap font-sans" x-text="fDesc"></pre>
                                </template>
                                @php
                                    $colors = [
                                        'high' => 'text-red-600 bg-red-50 border-red-100',
                                        'medium' => 'text-orange-500 bg-orange-50 border-orange-100',
                                        'low' => 'text-green-600 bg-green-50 border-green-100',
                                    ];
                                    $color = $colors[$task['priority']] ?? 'text-gray-500 bg-gray-100';
                                @endphp
                                <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $color }} mt-1 inline-block">
                                    {{ ucfirst($task['priority']) }}
                                </span>
                                @if(!empty($task['due_date']))
                                <span class="text-xs text-gray-400 ml-2">{{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}</span>
                                @endif
                                {{-- Subtasks preview --}}
                                <template x-if="subtasks.length > 0">
                                    <div class="mt-2 pl-1 space-y-0.5">
                                        <template x-for="(sub, si2) in subtasks" :key="si2">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span class="text-xs text-gray-500" x-text="sub.title"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="editing = true"
                                class="p-1 text-gray-300 hover:text-brand-500 hover:bg-brand-50 rounded transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="editing" x-cloak class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-brand-500">Editing Task</span>
                                <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600 transition">Done</button>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" x-model="fTitle" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description (Markdown)</label>
                                <textarea x-model="fDesc" rows="3"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none font-mono"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                                <select x-model="fPriority"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Due Date</label>
                                <input type="date" x-model="fDueDate"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            </div>
                            {{-- Subtasks editor --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Subtasks</label>
                                <div class="space-y-1.5">
                                    <template x-for="(sub, si2) in subtasks" :key="si2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" :checked="sub.approved == 1"
                                                @change="sub.approved = $event.target.checked ? 1 : 0"
                                                class="w-3.5 h-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            <input type="text" x-model="sub.title" :placeholder="'Subtask ' + (si2 + 1)"
                                                class="subtask-input flex-1 border border-gray-200 rounded px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                                            <button type="button" @click="removeSubtask(si2)"
                                                class="text-gray-300 hover:text-red-400 transition p-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="addSubtask()"
                                    class="mt-1.5 text-xs text-brand-500 hover:text-brand-600 font-medium transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add subtask
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="flex items-center gap-4 mt-6">
                <button type="submit"
                    class="flex-1 bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-3 rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve & Create Project
                </button>
                <a href="{{ route('ai.form') }}"
                    class="px-6 py-3 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
