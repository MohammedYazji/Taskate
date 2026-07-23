@props(['project' => null])

<form
    action="{{ $project ? route('projects.update', $project) : route('projects.store') }}"
    method="POST"
    class="flex flex-col flex-1 overflow-y-auto">
    @csrf
    @if($project) @method('PUT') @endif

    <div class="px-6 py-5 space-y-5 flex-1">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required maxlength="100"
                value="{{ $project->name ?? '' }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                placeholder="Project name...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
            <div class="flex items-center gap-1.5">
                <input type="color" value="{{ $project->color ?? '#7C3AED' }}"
                    oninput="document.getElementById('project-color').value = this.value"
                    class="w-9 h-9 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                <input type="text" id="project-color" name="color" value="{{ $project->color ?? '#7C3AED' }}" maxlength="7"
                    pattern="^#[0-9A-Fa-f]{6}$" required
                    oninput="this.previousElementSibling.value = this.value"
                    class="w-24 border border-gray-200 rounded-lg px-2 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" maxlength="500"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                placeholder="Project description...">{{ $project->description ?? '' }}</textarea>
        </div>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        <button type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 rounded-lg transition">
            {{ $project ? 'Save Changes' : 'Create Project' }}
        </button>
    </div>
</form>
