@props(['collapsed' => false])

<div x-data="{
    listModalOpen: false,
    listModalMode: 'add',
    listForm: { id: null, name: '', color: '#14B8A6', icon: '👋', view_type: 'list', folder_id: null },
    listMenuOpen: null,
    folderMenuOpen: null,
    folderOpen: {},
    tagModalOpen: false,
    tagModalMode: 'add',
    tagForm: { id: null, name: '', color: '#14B8A6', icon: '', parent_id: null },
    tagMenuOpen: null,
    renameModalOpen: false,
    renameFolderId: null,
    renameFolderName: '',
    newFolderName: '',
    showNewFolder: false,
    folderModalOpen: false,
    folderModalName: '',
    initSortable() {
        this.$nextTick(() => {
            document.querySelectorAll('.sortable-folder, .sortable-root').forEach(el => {
                if (el._sortable) return;
                el._sortable = new Sortable(el, {
                    group: 'sidebar-lists',
                    animation: 150,
                    ghostClass: 'opacity-30',
                    dragClass: 'shadow-lg',
                    draggable: '.sortable-item',
                    handle: '.drag-handle',
                    onEnd: function(evt) {
                        const itemId = evt.item.dataset.id;
                        const toFolderId = evt.to.dataset.folderId || null;
                        fetch('/projects/' + itemId, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ folder_id: toFolderId }),
                        }).then(() => location.reload());
                    }
                });
            });
        });
    },
    colors: ['#14B8A6','#3B82F6','#8B5CF6','#EF4444','#F59E0B','#EC4899','#6366F1','#10B981','#F97316','#06B6D4'],
    openAddList(folderId) {
        this.listModalMode = 'add';
        this.listForm = { id: null, name: '', color: '#14B8A6', icon: '👋', view_type: 'list', folder_id: folderId || null };
        this.showNewFolder = false;
        this.newFolderName = '';
        this.listModalOpen = true;
    },
    openEditList(project) {
        this.listModalMode = 'edit';
        this.listForm = { id: project.id, name: project.name, color: project.color, icon: project.icon || '👋', view_type: project.view_type || 'list', folder_id: project.folder_id };
        this.showNewFolder = false;
        this.newFolderName = '';
        this.listModalOpen = true;
    },
    saveList() {
        if (!this.listForm.name.trim()) return;
        const payload = { name: this.listForm.name, color: this.listForm.color, icon: this.listForm.icon, view_type: this.listForm.view_type, folder_id: this.listForm.folder_id };
        if (this.listModalMode === 'add') {
            fetch('/projects', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: JSON.stringify(payload) }).then(() => location.reload());
        } else {
            fetch('/projects/' + this.listForm.id, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PATCH' }, body: JSON.stringify(payload) }).then(() => location.reload());
        }
    },
    createFolder() {
        if (!this.newFolderName.trim()) return;
        fetch('/folders', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ name: this.newFolderName }) }).then(r => r.json()).then(f => { this.listForm.folder_id = f.id; this.showNewFolder = false; this.newFolderName = ''; });
    },
    createFolderModal() {
        if (!this.folderModalName.trim()) return;
        const name = this.folderModalName;
        const formData = { ...this.listForm, _name: name };
        sessionStorage.setItem('restoreListModal', JSON.stringify(formData));
        fetch('/folders', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ name: name }) }).then(() => {
            location.reload();
        });
    },
    renameFolder(id) {
        if (!this.renameFolderName.trim()) return;
        fetch('/folders/' + id, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ name: this.renameFolderName }) }).then(() => location.reload());
    },
    deleteFolder(id) {
        if (!confirm('Delete this folder? Lists will be moved to ungrouped.')) return;
        fetch('/folders/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload());
    }
}" class="flex-shrink-0 h-screen" x-init="initSortable(); $nextTick(() => {
    const saved = sessionStorage.getItem('restoreListModal');
    if (saved) {
        sessionStorage.removeItem('restoreListModal');
        const data = JSON.parse(saved);
        listModalMode = 'add';
        listForm = { id: null, name: data.name || '', color: data.color || '#14B8A6', icon: data.icon || '👋', view_type: data.view_type || 'list', folder_id: data.folder_id };
        listModalOpen = true;
    }
})">

<aside class="flex h-screen flex-shrink-0">

    {{-- Icon Rail --}}
    <div class="w-14 bg-gray-100 flex flex-col items-center py-4 gap-1 flex-shrink-0 border-r border-gray-200">

        <a href="{{ route('dashboard') }}" class="mb-4" title="Dashboard">
            <img src="{{ asset('logo.svg') }}" alt="Taskate" class="w-9 h-9">
        </a>

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
            <a href="{{ route('habits.index') }}" title="Habits"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('habits*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </a>
            <a href="{{ route('ai.form') }}" title="AI Generate"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition {{ request()->routeIs('ai.*') ? 'bg-brand-50 text-brand-600' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-200/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
            </a>
        </nav>

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

    {{-- Context Panel --}}
    @unless(request()->routeIs('habits*'))
    <div x-show="$store.sidebar.open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-4"
         class="w-64 bg-white flex flex-col h-screen flex-shrink-0 border-r border-gray-200">

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto px-2 pb-4 space-y-4 pt-4">

            {{-- Smart Views --}}
            <div>
                <a href="{{ route('smart.today') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smart.today') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm font-medium">Today</span>
                    @if(($todayCount ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded-full">{{ $todayCount }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.next7days') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smart.next7days') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1 text-sm font-medium">Next 7 Days</span>
                    @if(($next7Count ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded-full">{{ $next7Count }}</span>
                    @endif
                </a>
                <a href="{{ route('smart.inbox') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smart.inbox') ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <span class="flex-1 text-sm font-medium">Inbox</span>
                    @if(($inboxCount ?? 0) > 0)
                    <span class="text-[10px] font-semibold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">{{ $inboxCount }}</span>
                    @endif
                </a>
            </div>

            {{-- Lists Header --}}
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Lists</h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] text-gray-400">{{ count($projects ?? []) }}</span>
                        <button @click="openAddList()" class="w-4 h-4 rounded flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition" title="Add List">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Folder groups --}}
                @foreach($folders as $folder)
                @php $folderProjects = $projects->filter(fn($p) => $p->folder_id === $folder->id); @endphp
                <div class="mb-1">
                    <div class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-gray-500 group/folder">
                        <button @click="folderOpen[{{ $folder->id }}] = !folderOpen[{{ $folder->id }}]"
                            class="w-4 h-4 flex items-center justify-center rounded transition text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3 transition-transform" :class="folderOpen[{{ $folder->id }}] === false ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <span class="flex-1 truncate">{{ $folder->name }}</span>
                        <div class="relative" @click.outside="folderMenuOpen = null">
                            <button @click.stop="folderMenuOpen = folderMenuOpen === {{ $folder->id }} ? null : {{ $folder->id }}"
                                class="opacity-0 group-hover/folder:opacity-100 w-5 h-5 flex items-center justify-center rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                            <div x-cloak x-show="folderMenuOpen === {{ $folder->id }}"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                <button @click="openAddList({{ $folder->id }}); folderMenuOpen = null" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add List
                                </button>
                                <button @click="renameFolderId = {{ $folder->id }}; renameFolderName = '{{ addslashes($folder->name) }}'; folderMenuOpen = null; renameModalOpen = true" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                                <button @click="fetch('/folders/{{ $folder->id }}/pin', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload())"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    {{ $folder->pinned ? 'Unpin' : 'Pin' }}
                                </button>
                                <div class="border-t border-gray-100 my-0.5"></div>
                                <button @click="if(confirm('Ungroup this folder? Lists will be moved to ungrouped.')) { fetch('/folders/{{ $folder->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload()) }"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Ungroup
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($folderProjects->count() > 0)
                    <div x-show="folderOpen[{{ $folder->id }}] !== false" x-transition>
                    <div class="sortable-folder" data-folder-id="{{ $folder->id }}">
                    @foreach($folderProjects as $project)
                    <div class="group relative flex items-center gap-1 pl-3 pr-3 py-1.5 rounded-lg transition sortable-item {{ request()->routeIs('projects.show', $project) ? 'text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}" data-id="{{ $project->id }}">
                        <span class="drag-handle opacity-0 group-hover:opacity-100 cursor-grab active:cursor-grabbing flex-shrink-0 p-0.5 rounded text-gray-300 hover:text-gray-500 transition">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                        </span>
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-sm flex-shrink-0">{{ $project->icon ?? '👋' }}</span>
                            <span class="flex-1 text-sm truncate">{{ $project->name }}</span>
                            @if($project->tasks_count > 0)
                            <span class="text-[10px] text-gray-400">{{ $project->tasks_count }}</span>
                            @endif
                        </a>
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#14B8A6' }}"></span>
                        <div class="relative" @click.outside="listMenuOpen = null">
                            <button @click.stop="listMenuOpen = listMenuOpen === {{ $project->id }} ? null : {{ $project->id }}"
                                class="opacity-0 group-hover:opacity-100 p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                            <div x-cloak x-show="listMenuOpen === {{ $project->id }}"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                <button @click="openEditList({{ json_encode($project) }}); listMenuOpen = null" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                                <button @click="fetch('/projects/{{ $project->id }}/pin', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload())"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    {{ $project->pinned ? 'Unpin' : 'Pin' }}
                                </button>
                                <button @click="fetch('/projects/{{ $project->id }}/duplicate', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload())"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Duplicate
                                </button>
                                <div class="border-t border-gray-100 my-0.5"></div>
                                <button @click="if(confirm('Delete this list?')) { fetch('/projects/{{ $project->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => { if (window.location.pathname === '/projects/{{ $project->id }}') window.location.href = '/dashboard'; else location.reload(); }) }"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
                    </div>
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Ungrouped lists --}}
                <div class="sortable-root" id="sidebar-ungrouped">
                @foreach($projects->whereNull('folder_id') ?? [] as $project)
                <div class="group relative flex items-center gap-1 px-3 py-2 rounded-lg transition sortable-item {{ request()->routeIs('projects.show', $project) ? 'text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}" data-id="{{ $project->id }}">
                    <span class="drag-handle opacity-0 group-hover:opacity-100 cursor-grab active:cursor-grabbing flex-shrink-0 p-0.5 rounded text-gray-300 hover:text-gray-500 transition">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                    </span>
                    <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2 flex-1 min-w-0">
                        <span class="text-sm flex-shrink-0">{{ $project->icon ?? '👋' }}</span>
                        <span class="flex-1 text-sm truncate">{{ $project->name }}</span>
                        @if($project->tasks_count > 0)
                        <span class="text-[10px] text-gray-400">{{ $project->tasks_count }}</span>
                        @endif
                    </a>
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#14B8A6' }}"></span>
                    <div class="relative" @click.outside="listMenuOpen = null">
                        <button @click.stop="listMenuOpen = listMenuOpen === {{ $project->id }} ? null : {{ $project->id }}"
                            class="opacity-0 group-hover:opacity-100 p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                        </button>
                        <div x-cloak x-show="listMenuOpen === {{ $project->id }}"
                            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                            <button @click="openEditList({{ json_encode($project) }}); listMenuOpen = null" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button @click="fetch('/projects/{{ $project->id }}/pin', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload())"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                {{ $project->pinned ? 'Unpin' : 'Pin' }}
                            </button>
                            <button @click="fetch('/projects/{{ $project->id }}/duplicate', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload())"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Duplicate
                            </button>
                            <div class="border-t border-gray-100 my-0.5"></div>
                            <button @click="if(confirm('Delete this list?')) { fetch('/projects/{{ $project->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => { if (window.location.pathname === '/projects/{{ $project->id }}') window.location.href = '/dashboard'; else location.reload(); }) }"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>

                @if(count($projects ?? []) === 0)
                <p class="px-3 py-2 text-xs text-gray-400">No lists yet</p>
                @endif
            </div>

            {{-- Tags --}}
            @if(count($tags ?? []) > 0)
            <div>
                <div class="flex items-center justify-between px-3 mb-1">
                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Tags</h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] text-gray-400">{{ count($tags) }}</span>
                        <button @click="tagModalOpen = true; tagModalMode = 'add'; tagForm = { id: null, name: '', color: '#14B8A6', icon: '', parent_id: null }"
                            class="w-4 h-4 rounded flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition" title="Add Tag">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>
                @foreach($tags as $tag)
                <div class="group relative flex items-center gap-3 px-3 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                    <span class="flex-1 text-sm truncate">{{ $tag->icon ?: '🏷️' }} {{ $tag->name }}</span>
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></span>
                    <div class="relative opacity-0 group-hover:opacity-100 transition" @click.outside="tagMenuOpen = null">
                        <button @click.stop="tagMenuOpen = tagMenuOpen === {{ $tag->id }} ? null : {{ $tag->id }}"
                            class="p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                        </button>
                        <div x-cloak x-show="tagMenuOpen === {{ $tag->id }}"
                            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                            <button @click="tagModalOpen = true; tagModalMode = 'edit'; tagForm = { id: {{ $tag->id }}, name: '{{ addslashes($tag->name) }}', color: '{{ $tag->color }}', icon: '{{ addslashes($tag->icon ?? '') }}', parent_id: {{ $tag->parent_id ?? 'null' }} }; tagMenuOpen = null"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button @click="tagModalOpen = true; tagModalMode = 'add'; tagForm = { id: null, name: '', color: '#14B8A6', icon: '', parent_id: {{ $tag->id }} }; tagMenuOpen = null"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add subtag
                            </button>
                            <div class="border-t border-gray-100 my-0.5"></div>
                            <button @click="if(confirm('Delete this tag?')) { fetch('/tags/{{ $tag->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' } }).then(() => location.reload()) }"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Pinned Bottom --}}
        <div class="px-2 pb-2 space-y-0.5 flex-shrink-0 border-t border-gray-100 pt-2">
            <a href="/completed" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 text-sm hover:bg-gray-50 hover:text-gray-600 transition">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="flex-1">Completed</span>
                @if(($completedTasks ?? 0) > 0)
                <span class="text-[10px]">{{ $completedTasks }}</span>
                @endif
            </a>
            <div x-data="{ userMenuOpen: false }" class="relative">
                <button @click="userMenuOpen = !userMenuOpen" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition cursor-pointer text-left">
                    <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <span class="flex-1 text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="userMenuOpen" x-cloak @click.outside="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    class="absolute bottom-full left-0 right-0 mb-1 mx-1 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-50">
                    <div class="px-3 py-2 border-b border-gray-100">
                        <div class="text-xs font-medium text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endunless

</aside>

{{-- ==================== TAG MODAL ==================== --}}
<div x-show="tagModalOpen" x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="tagModalOpen = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        @click.outside="tagModalOpen = false">
        <div class="px-6 pt-6 pb-4">
            <h3 class="text-lg font-semibold text-gray-900 text-center" x-text="tagModalMode === 'add' ? 'Add Tag' : 'Edit Tag'"></h3>
        </div>
        <div class="px-6 pb-6 space-y-5">
            {{-- Name --}}
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</label>
                <input type="text" x-model="tagForm.name" placeholder="Tag name..."
                    class="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
            </div>
            {{-- Icon --}}
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Icon</label>
                <input type="text" x-model="tagForm.icon" placeholder="Emoji icon..."
                    class="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
            </div>
            {{-- Color --}}
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Color</label>
                <div class="flex gap-2 mt-1.5">
                    <template x-for="c in ['#14B8A6','#3B82F6','#8B5CF6','#EF4444','#F59E0B','#EC4899','#6366F1','#10B981','#F97316','#06B6D4','#64748B','#84CC16']" :key="c">
                        <button type="button" @click="tagForm.color = c"
                            class="w-7 h-7 rounded-full border-2 transition"
                            :class="tagForm.color === c ? 'border-gray-900 scale-110' : 'border-transparent'"
                            :style="'background-color:' + c"></button>
                    </template>
                </div>
            </div>
            {{-- Parent Tag --}}
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Parent Tag</label>
                <select x-model="tagForm.parent_id"
                    class="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700">
                    <option :value="null">None</option>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->icon ? $tag->icon . ' ' : '' }}{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button @click="tagModalOpen = false" type="button"
                    class="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button @click="(() => {
                    const url = tagModalMode === 'add' ? '/tags' : '/tags/' + tagForm.id;
                    const method = tagModalMode === 'add' ? 'POST' : 'PATCH';
                    fetch(url, {
                        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ name: tagForm.name, color: tagForm.color, icon: tagForm.icon || null, parent_id: tagForm.parent_id || null })
                    }).then(() => location.reload());
                })()" type="button"
                    class="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">
                    <span x-text="tagModalMode === 'add' ? 'Create Tag' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== ADD/EDIT LIST MODAL ==================== --}}
    <div x-show="listModalOpen" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="listModalOpen = false"></div>

        {{-- Modal Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            @click.outside="if(!folderModalOpen) listModalOpen = false">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 text-center" x-text="listModalMode === 'add' ? 'Add List' : 'Edit List'"></h3>
            </div>

            <div class="px-6 pb-6 space-y-5">

                {{-- Name --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</label>
                    <input type="text" x-model="listForm.name" placeholder="List name..."
                        class="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
                </div>

                {{-- Icon Picker --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Icon</label>
                    <div class="mt-1.5 flex items-center gap-3">
                        <button type="button" @click="$refs.emojiInput.focus()"
                            class="w-14 h-14 rounded-xl border-2 border-gray-200 flex items-center justify-center text-3xl hover:border-brand-500 transition flex-shrink-0"
                            :class="listForm.icon ? 'border-brand-500 bg-brand-50' : ''">
                            <span x-text="listForm.icon || '👋'"></span>
                        </button>
                        <div class="flex-1 space-y-1.5">
                            <input x-ref="emojiInput" type="text" x-model="listForm.icon"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300"
                                placeholder="Type or paste an emoji...">
                            <p class="text-[10px] text-gray-400">On desktop press <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[9px]">Win</kbd> + <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[9px]">.</kbd> or <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[9px]">Ctrl</kbd> + <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[9px]">Cmd</kbd> + <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[9px]">Space</kbd> for emoji picker</p>
                        </div>
                    </div>
                </div>

                {{-- Color Picker --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Color</label>
                    <div class="flex gap-2 mt-1.5">
                        <template x-for="c in colors" :key="c">
                            <button type="button" @click="listForm.color = c"
                                class="w-7 h-7 rounded-full border-2 transition"
                                :class="listForm.color === c ? 'border-gray-900 scale-110' : 'border-transparent'"
                                :style="'background-color:' + c"></button>
                        </template>
                    </div>
                </div>

                {{-- View Type --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">View Type</label>
                    <div class="flex gap-2 mt-1.5">
                        <button type="button" @click="listForm.view_type = 'list'"
                            class="flex-1 text-xs py-2 rounded-lg border transition"
                            :class="listForm.view_type === 'list' ? 'border-brand-500 bg-brand-50 text-brand-600 font-medium' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                            List
                        </button>
                        <button type="button" @click="listForm.view_type = 'kanban'"
                            class="flex-1 text-xs py-2 rounded-lg border transition"
                            :class="listForm.view_type === 'kanban' ? 'border-brand-500 bg-brand-50 text-brand-600 font-medium' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                            Board
                        </button>
                        <button type="button" @click="listForm.view_type = 'timeline'"
                            class="flex-1 text-xs py-2 rounded-lg border transition"
                            :class="listForm.view_type === 'timeline' ? 'border-brand-500 bg-brand-50 text-brand-600 font-medium' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                            Timeline
                        </button>
                    </div>
                </div>

                {{-- Folder --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Folder</label>
                    <div class="mt-1.5">
                        <select x-model="listForm.folder_id" @change="if(listForm.folder_id === '__new__') { folderModalOpen = true; listForm.folder_id = null; }"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700">
                            <option :value="null">No folder</option>
                            @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                            <option value="__new__">+ Create new folder</option>
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-2">
                    <button @click="listModalOpen = false" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="saveList()" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">
                        <span x-text="listModalMode === 'add' ? 'Create List' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== CREATE FOLDER POPUP ==================== --}}
    <div x-show="folderModalOpen" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="folderModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            @click.outside="folderModalOpen = false">
            <div class="px-6 pt-6 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 text-center">New Folder</h3>
            </div>
            <div class="px-6 pb-6 space-y-4">
                <input type="text" x-model="folderModalName" x-init="$watch('folderModalOpen', v => { if(v) $nextTick(() => $el.focus()) })"
                    @keydown.enter="createFolderModal()"
                    placeholder="Folder name..."
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
                <div class="flex gap-3">
                    <button @click="folderModalOpen = false" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="createFolderModal()" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== RENAME FOLDER POPUP ==================== --}}
    <div x-show="renameModalOpen" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="renameModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            @click.outside="renameModalOpen = false">
            <div class="px-6 pt-6 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 text-center">Edit Folder</h3>
            </div>
            <div class="px-6 pb-6 space-y-4">
                <input type="text" x-model="renameFolderName" x-init="$watch('renameModalOpen', v => { if(v) $nextTick(() => $el.focus()) })"
                    @keydown.enter="renameFolder(renameFolderId)"
                    placeholder="Folder name..."
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300">
                <div class="flex gap-3">
                    <button @click="renameModalOpen = false" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="renameFolder(renameFolderId)" type="button"
                        class="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
@endpush
