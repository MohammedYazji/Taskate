<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">AI Task Generator</h1>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-red-700">Generation failed</p>
                @if($generation->error)
                <p class="text-xs text-red-500 max-w-md">{{ $generation->error }}</p>
                @endif
                <a href="{{ route('ai.form') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium mt-2">Try again</a>
            </div>
        </div>

        <div class="mt-4 bg-gray-50 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400">Topic: <span class="text-gray-600">{{ $generation->topic }}</span></p>
        </div>
    </div>
</x-app-layout>
