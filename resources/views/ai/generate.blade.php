<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">AI Task Generator</h1>
            <p class="text-sm text-gray-500 mt-1">Describe a topic and AI will break it down into actionable tasks</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('ai.generate') }}">
                @csrf
                <div class="mb-6">
                    <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">Topic / Project</label>
                    <textarea
                        id="topic"
                        name="topic"
                        rows="4"
                        required
                        minlength="5"
                        maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none"
                        placeholder="e.g., Build a REST API for an e-commerce platform with user authentication, product management, and order processing...">{{ old('topic') }}
                    </textarea>
                    @error('topic')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium py-3 rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Tasks
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
