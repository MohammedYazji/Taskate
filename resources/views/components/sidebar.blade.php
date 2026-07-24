@props(['collapsed' => false])

<aside class="flex h-screen flex-shrink-0">

    {{-- Icon Rail — light gray --}}
    <div class="w-14 bg-gray-100 flex flex-col items-center py-4 gap-1 flex-shrink-0 border-r border-gray-200">

        {{-- User Avatar --}}
        <a href="{{ route('dashboard') }}" class="mb-4" title="Dashboard">
            <img src="{{ asset('logo.svg') }}" alt="Taskate" class="w-9 h-9">
        </a>

        {{-- Nav Icons --}}
        <nav class="flex-1 flex flex-col items-center gap-1 w-full px-2">
            <a href="{{ route('dashboard') }}" title="Dashboard"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </a>
            <a href="{{ route('projects.index') }}" title="Projects"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('projects.*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </a>
            <a href="{{ route('eisenhower.index') }}" title="Matrix"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('eisenhower*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </a>
            <a href="{{ route('pomodoro.index') }}" title="Pomodoro"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('pomodoro*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </a>
            <a href="{{ route('search') }}" title="Search"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('search*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </a>
        </nav>

        {{-- Bottom Icons --}}
        <div class="flex flex-col items-center gap-1 px-2">
            <a href="{{ route('tags.index') }}" title="Tags"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('tags.*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            </a>
            <button title="Notifications"
                class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 transition relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full"></span>
            </button>
        </div>
    </div>

    {{-- Context Panel — white sidebar --}}
    <div class="w-64 bg-white flex flex-col h-screen flex-shrink-0 border-r border-gray-200">

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto px-2 pb-4 space-y-4 pt-4">

            {{-- Smart Views --}}
            <div>
                <a href="{{ route('smart.today') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.today') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm font-medium">Today</span>
                    @if(($todayCount ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded-full">{{ $todayCount }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.next7days') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.next7days') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm font-medium">Next 7 Days</span>
                    @if(($next7Count ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded-full">{{ $next7Count }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.inbox') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition group {{ request()->routeIs('smart.inbox') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <span class="flex-1 text-sm font-medium">Inbox</span>
                    @if(($inboxCount ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">{{ $inboxCount }}</span>
                    @endif
                </a>
            </div>

            {{-- Lists --}}
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Lists</h3>
                    <span class="text-[10px] text-gray-400">{{ count($projects ?? []) }}</span>
                </div>
                @foreach($projects ?? [] as $project)
                <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('projects.show', $project) ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#14B8A6' }}"></div>
                    <span class="flex-1 text-sm truncate">{{ $project->name }}</span>
                    @if($project->tasks_count > 0)
                    <span class="text-[10px] text-gray-400">{{ $project->tasks_count }}</span>
                    @endif
                </a>
                @endforeach
                @if(count($projects ?? []) === 0)
                <p class="px-3 py-2 text-xs text-gray-400">No projects yet</p>
                @endif
            </div>

            {{-- Tags --}}
            @if(count($tags ?? []) > 0)
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Tags</h3>
                    <span class="text-[10px] text-gray-400">{{ count($tags) }}</span>
                </div>
                @foreach($tags as $tag)
                <a href="{{ route('tags.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                    <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></div>
                    <span class="flex-1 text-sm truncate">{{ $tag->name }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Pinned Bottom --}}
        <div class="px-2 pb-2 space-y-0.5 flex-shrink-0 border-t border-gray-100 pt-2">
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="flex-1">Completed</span>
                @if(($completedTasks ?? 0) > 0)
                <span class="text-[10px]">{{ $completedTasks }}</span>
                @endif
            </div>

            {{-- User Avatar --}}
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <span class="flex-1 text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

</aside>
