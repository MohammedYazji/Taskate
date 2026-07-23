<x-app-layout>
    <div class="max-w-lg">

        {{-- Header --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Tags</h1>

        {{-- Inline Create Form --}}
        <form method="POST" action="{{ route('tags.store') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                <input type="text" name="name" required maxlength="50"
                    placeholder="e.g. Design"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Color</label>
                <div class="flex items-center gap-1.5">
                    <input type="color" value="#7C3AED"
                        oninput="document.getElementById('tag-color').value = this.value"
                        class="w-9 h-9 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                    <input type="text" id="tag-color" name="color" value="#7C3AED" maxlength="7"
                        pattern="^#[0-9A-Fa-f]{6}$"
                        oninput="this.previousElementSibling.value = this.value"
                        class="w-22 border border-gray-200 rounded-lg px-2 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono">
                </div>
                @error('color')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition whitespace-nowrap">
                Add Tag
            </button>
        </form>

        {{-- Tag List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($tags as $tag)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition group">

                {{-- Color Dot --}}
                <span class="w-4 h-4 rounded-full flex-shrink-0 ring-2 ring-white shadow-sm"
                    style="background-color: {{ $tag->color }}"></span>

                {{-- Name --}}
                <span class="flex-1 text-sm text-gray-800">{{ $tag->name }}</span>

                {{-- Color Hex --}}
                <span class="text-xs text-gray-400 font-mono">{{ $tag->color }}</span>

                {{-- Delete --}}
                <form method="POST" action="{{ route('tags.destroy', $tag) }}"
                    onsubmit="return confirm('Delete the "{{ $tag->name }}" tag?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                        title="Delete tag">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>

            </div>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                No tags yet — create your first one above!
            </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
