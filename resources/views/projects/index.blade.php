<x-app-layout>
    <div x-data="{ open: false }" class="max-w-5xl">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
            <button @click="open = true"
                class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <span class="text-lg leading-none">+</span> New Project
            </button>
        </div>

        @if($projects->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4v4H3zM3 14h4v4H3zM10 7h11M10 12h11M10 17h11"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 mb-1">No projects yet</h2>
                <p class="text-sm text-gray-400">Create a project to organize your tasks</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects as $project)
                <a href="{{ route('projects.show', $project) }}"
                    class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-gray-300 transition block group">

                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" style="background-color: {{ $project->color }}"></span>
                            <h3 class="font-semibold text-gray-900 truncate">{{ $project->name }}</h3>
                        </div>
                        <span class="text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition">
                            {{ $project->updated_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($project->description)
                    <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $project->description }}</p>
                    @endif

                    <div>
                        <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                            <span>{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tasks</span>
                            <span>{{ $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-violet-600 rounded-full transition-all"
                                style="width: {{ $project->tasks_count > 0 ? ($project->completed_tasks_count / $project->tasks_count * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>

                </a>
                @endforeach
            </div>
        @endif

        {{-- Overlay --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 bg-black/30 z-40">
        </div>

        {{-- Slide-in Panel --}}
        <div x-show="open" x-cloak @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">New Project</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <x-project-form />
        </div>

    </div>
</x-app-layout>
