@props(['collapsed' => false])

<aside class="flex h-screen flex-shrink-0" x-data="{ collapsed: false }">

    {{-- Icon Rail --}}
    <div class="w-16 bg-sky-800 flex flex-col items-center py-4 gap-1 flex-shrink-0">

        {{-- User Avatar --}}
        <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-xl bg-sky-500 flex items-center justify-center text-sm font-bold text-white mb-4 hover:bg-mint-300 transition" title="Dashboard">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </a>

        {{-- Nav Icons --}}
        <nav class="flex-1 flex flex-col items-center gap-1 w-full px-2">
            <a href="{{ route('calendar') }}" title="Today"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('calendar*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('eisenhower.index') }}" title="Dashboard"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('eisenhower*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </a>
            <a href="{{ route('projects.index') }}" title="Projects"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('projects.*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </a>
            <a href="{{ route('eisenhower.index') }}" title="Matrix"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('eisenhower*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
            </a>
            <a href="{{ route('pomodoro.index') }}" title="Pomodoro"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('pomodoro*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </a>
            <a href="{{ route('search') }}" title="Search"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('search*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </a>
        </nav>

        {{-- Bottom Icons --}}
        <div class="flex flex-col items-center gap-1 px-2">
            <a href="{{ route('calendar') }}" title="Calendar"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('calendar*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('tags.index') }}" title="Tags"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('tags.*') ? 'bg-sky-500/30 text-sky-300' : 'text-sky-400/60 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            </a>
            <button title="Notifications"
                class="w-10 h-10 rounded-xl flex items-center justify-center text-sky-400/60 hover:text-white hover:bg-white/5 transition relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-coral-300 rounded-full"></span>
            </button>
        </div>
    </div>

    {{-- Context Panel --}}
    <div class="w-64 bg-sky-800 flex flex-col h-screen flex-shrink-0 border-r border-sky-700/50">

        {{-- Logo + New Task --}}
        <div class="px-4 pt-4 pb-3 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-white tracking-tight">Taskate</span>
                <button onclick="document.dispatchEvent(new CustomEvent('open-task-panel'))"
                    class="w-7 h-7 rounded-lg bg-sky-500 hover:bg-mint-300 flex items-center justify-center text-white text-sm transition" title="New Task">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto px-2 pb-4 space-y-4">

            {{-- Smart Views --}}
            <div>
                <a href="{{ route('smart.today') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.today') ? 'bg-sky-500/20 text-coral-300' : 'text-sky-300/80 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm">Today</span>
                    @if(($todayCount ?? 0) > 0)
                    <span class="text-[10px] font-medium bg-coral-300/20 text-coral-300 px-1.5 py-0.5 rounded-full">{{ $todayCount }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.next7days') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.next7days') ? 'bg-sky-500/20 text-mint-300' : 'text-sky-300/80 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm">Next 7 Days</span>
                    @if(($next7Count ?? 0) > 0)
                    <span class="text-[10px] font-medium bg-mint-300/20 text-mint-300 px-1.5 py-0.5 rounded-full">{{ $next7Count }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.inbox') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.inbox') ? 'bg-sky-500/20 text-sky-300' : 'text-sky-300/80 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <span class="flex-1 text-sm">Inbox</span>
                    @if(($inboxCount ?? 0) > 0)
                    <span class="text-[10px] font-medium bg-sky-500/30 text-sky-300 px-1.5 py-0.5 rounded-full">{{ $inboxCount }}</span>
                    @endif
                </a>
            </div>

            {{-- Lists --}}
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-sky-400/50">Lists</h3>
                    <span class="text-[10px] text-sky-400/40">{{ count($projects ?? []) }}</span>
                </div>
                @foreach($projects ?? [] as $project)
                <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('projects.show', $project) ? 'bg-sky-500/20 text-coral-300' : 'text-sky-300/80 hover:text-white hover:bg-white/5' }}">
                    <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#67A2C5' }}"></div>
                    <span class="flex-1 text-sm truncate">{{ $project->name }}</span>
                    @if($project->tasks_count > 0)
                    <span class="text-[10px] text-sky-400/50">{{ $project->tasks_count }}</span>
                    @endif
                </a>
                @endforeach
                @if(count($projects ?? []) === 0)
                <p class="px-3 py-2 text-xs text-sky-400/40">No projects yet</p>
                @endif
            </div>

            {{-- Tags --}}
            @if(count($tags ?? []) > 0)
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-sky-400/50">Tags</h3>
                    <span class="text-[10px] text-sky-400/40">{{ count($tags) }}</span>
                </div>
                @foreach($tags as $tag)
                <a href="{{ route('tags.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-sky-300/80 hover:text-white hover:bg-white/5">
                    <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></div>
                    <span class="flex-1 text-sm truncate">{{ $tag->name }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Pinned Bottom --}}
        <div class="px-2 pb-2 space-y-0.5 flex-shrink-0 border-t border-sky-700/50 pt-2">
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-sky-400/50 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="flex-1">Completed</span>
                @if(($completedTasks ?? 0) > 0)
                <span class="text-[10px]">{{ $completedTasks }}</span>
                @endif
            </div>
            <button onclick="document.dispatchEvent(new CustomEvent('open-task-panel'))"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg bg-sky-500/20 text-sky-300 hover:bg-mint-300/20 hover:text-mint-300 transition text-sm mt-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Task</span>
            </button>
        </div>
    </div>

</aside>
