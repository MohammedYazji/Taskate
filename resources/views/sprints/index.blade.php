<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
                <p class="text-sm text-gray-500">Sprints</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        {{-- Active Sprint --}}
        @if($activeSprint)
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        <h2 class="font-semibold text-gray-900">{{ $activeSprint->name }}</h2>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                    </div>
                    @if($activeSprint->goal)
                    <p class="text-sm text-gray-500 mt-1">{{ $activeSprint->goal }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $activeSprint->start_date->format('M j') }} – {{ $activeSprint->end_date->format('M j') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Sprint List --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @forelse($sprints as $sprint)
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full {{ $sprint->status->value === 'active' ? 'bg-green-500' : ($sprint->status->value === 'completed' ? 'bg-gray-400' : 'bg-yellow-500') }}"></span>
                        <h3 class="text-sm font-medium text-gray-900">{{ $sprint->name }}</h3>
                        <span class="text-xs text-gray-400">{{ ucfirst($sprint->status->value) }}</span>
                    </div>
                    @if($sprint->goal)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $sprint->goal }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $sprint->start_date->format('M j') }} – {{ $sprint->end_date->format('M j') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($sprint->status->value !== 'active' && $sprint->status->value !== 'completed')
                    <form method="POST" action="{{ route('sprints.activate', $sprint) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs text-green-600 hover:text-green-700 font-medium">Activate</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('sprints.destroy', $sprint) }}"
                        onsubmit="return confirm('Delete this sprint? Tasks will move to backlog.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="text-xs text-red-500 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                No sprints yet — create your first one!
            </div>
            @endforelse
        </div>

        {{-- Create Sprint --}}
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">New Sprint</h3>
            <form method="POST" action="{{ route('projects.sprints.store', $project) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Goal (optional)</label>
                        <textarea name="goal" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Create Sprint
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4">
            <a href="{{ route('projects.index') }}"
                class="text-sm text-brand-500 hover:text-brand-600">&larr; Back to Projects</a>
        </div>
    </div>
</x-app-layout>
