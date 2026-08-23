import { useState, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import DatePicker from '@/Components/DatePicker';
import PriorityPicker from '@/Components/PriorityPicker';
import TaskEditPanel from '@/Components/TaskEditPanel';

const todayStr = new Date().toISOString().split('T')[0];

const PRIORITY_COLORS = {
    low: 'text-green-500',
    medium: 'text-yellow-500',
    high: 'text-red-500',
};

const SORT_OPTIONS = [
    { value: '', label: 'Latest' },
    { value: 'priority', label: 'Priority' },
    { value: 'date_asc', label: 'Due date (asc)' },
    { value: 'date_desc', label: 'Due date (desc)' },
];

export default function Tasks({ tasks: initialTasks, tags, projects, filters }) {
    const [tasks, setTasks] = useState(initialTasks);
    const [editTask, setEditTask] = useState(null);
    const [filterOpen, setFilterOpen] = useState(false);
    const [sortOpen, setSortOpen] = useState(false);
    const [newOpen, setNewOpen] = useState(false);
    const [newTitle, setNewTitle] = useState('');
    const [newPriority, setNewPriority] = useState('medium');
    const [newDate, setNewDate] = useState(filters?.date || '');
    const [newProjectId, setNewProjectId] = useState('');
    const [newTagIds, setNewTagIds] = useState([]);

    const hasFilters = !!(filters?.priority || filters?.status || filters?.date);

    const applyFilters = (changes) => {
        router.get(route('tasks.index'), { ...filters, ...changes }, { preserveState: true, replace: true });
    };

    const clearFilters = () => {
        router.get(route('tasks.index'), {}, { preserveState: true, replace: true });
    };

    const toggleStatus = useCallback((task) => {
        const newStatus = task.status === 'done' ? 'todo' : 'done';
        const updated = { ...task, status: newStatus };
        setTasks((prev) => prev.map((t) => (t.id === task.id ? updated : t)));
        if (editTask?.id === task.id) setEditTask(updated);
        router.patch(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true, preserveState: true });
    }, [editTask]);

    const deleteTask = useCallback((task) => {
        if (!confirm('Are you sure you want to delete this task?')) return;
        router.delete(`/tasks/${task.id}`, { preserveScroll: true, preserveState: true });
        setTasks((prev) => prev.filter((t) => t.id !== task.id));
        if (editTask?.id === task.id) setEditTask(null);
    }, [editTask]);

    const openEdit = useCallback((task) => {
        setEditTask(JSON.parse(JSON.stringify(task)));
    }, []);

    const handleTaskUpdate = useCallback((updated) => {
        setTasks((prev) => prev.map((t) => (t.id === updated.id ? updated : t)));
    }, []);

    const toggleNewTag = (tagId) => {
        setNewTagIds((prev) =>
            prev.includes(tagId) ? prev.filter((id) => id !== tagId) : [...prev, tagId]
        );
    };

    const createTask = () => {
        if (!newTitle.trim()) return;
        router.post('/tasks', {
            title: newTitle,
            priority: newPriority,
            due_date: newDate || null,
            project_id: newProjectId || null,
            tag_ids: newTagIds,
            is_recurring: false,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setNewOpen(false);
                setNewTitle('');
                setNewPriority('medium');
                setNewDate(filters?.date || '');
                setNewProjectId('');
                setNewTagIds([]);
            },
        });
    };

    return (
        <div className="max-w-4xl">
            <Head title="Tasks" />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-semibold text-gray-900">My Tasks</h1>
                <button
                    onClick={() => setNewOpen(true)}
                    className="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2"
                >
                    <span className="text-lg leading-none">+</span> New Task
                </button>
            </div>

            <div className="flex items-center gap-2 mb-4">
                <div className="relative">
                    <button
                        onClick={() => { setFilterOpen(!filterOpen); setSortOpen(false); }}
                        className="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5"
                    >
                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                        {hasFilters && <span className="w-1.5 h-1.5 bg-brand-500 rounded-full" />}
                    </button>
                    {filterOpen && (
                        <>
                            <div className="fixed inset-0 z-10" onClick={() => setFilterOpen(false)} />
                            <div className="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 min-w-48 z-20">
                                <div className="space-y-2">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                                        <select
                                            value={filters?.priority || ''}
                                            onChange={(e) => applyFilters({ priority: e.target.value || undefined })}
                                            className="w-full text-xs border border-gray-200 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-brand-500"
                                        >
                                            <option value="">All</option>
                                            <option value="high">High</option>
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                        <select
                                            value={filters?.status || ''}
                                            onChange={(e) => applyFilters({ status: e.target.value || undefined })}
                                            className="w-full text-xs border border-gray-200 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-brand-500"
                                        >
                                            <option value="">All</option>
                                            <option value="todo">Todo</option>
                                            <option value="done">Done</option>
                                        </select>
                                    </div>
                                    {hasFilters && (
                                        <button
                                            onClick={clearFilters}
                                            className="block text-xs text-brand-500 hover:text-brand-600 mt-2"
                                        >
                                            Clear filters
                                        </button>
                                    )}
                                </div>
                            </div>
                        </>
                    )}
                </div>

                <div className="relative">
                    <button
                        onClick={() => { setSortOpen(!sortOpen); setFilterOpen(false); }}
                        className="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5"
                    >
                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7h6M3 12h12M3 17h8" />
                        </svg>
                        Sort
                        {filters?.sort && <span className="w-1.5 h-1.5 bg-brand-500 rounded-full" />}
                    </button>
                    {sortOpen && (
                        <>
                            <div className="fixed inset-0 z-10" onClick={() => setSortOpen(false)} />
                            <div className="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 min-w-48 z-20">
                                <div className="space-y-1">
                                    {SORT_OPTIONS.map((opt) => (
                                        <label key={opt.value} className="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="sort"
                                                checked={(filters?.sort || '') === opt.value}
                                                onChange={() => { applyFilters({ sort: opt.value || undefined }); setSortOpen(false); }}
                                                className="text-brand-500 focus:ring-brand-500"
                                            />
                                            <span className="text-xs text-gray-600">{opt.label}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>

            <div className="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                {tasks.length === 0 && (
                    <div className="px-5 py-10 text-center text-gray-400 text-sm">
                        No tasks yet, create your first one!
                    </div>
                )}
                {tasks.map((task) => (
                    <div
                        key={task.id}
                        className={`flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition cursor-pointer group ${
                            task.due_date && task.due_date < todayStr && task.status !== 'done' ? 'bg-red-50/40' : ''
                        }`}
                        onClick={() => openEdit(task)}
                    >
                        <button
                            onClick={(e) => { e.stopPropagation(); toggleStatus(task); }}
                            className={`w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                task.status === 'done' ? 'bg-brand-500 border-brand-500 hover:bg-brand-600' : 'border-gray-300 hover:border-brand-400'
                            }`}
                        >
                            {task.status === 'done' && (
                                <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                </svg>
                            )}
                        </button>

                        <span className={`flex-1 min-w-0 text-sm truncate ${task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                            {task.title}
                        </span>

                        {task.project_name && (
                            <span className="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0">
                                {task.project_name}
                            </span>
                        )}

                        {task.subtasks?.length > 0 && (
                            <span className="text-[10px] text-gray-400 flex-shrink-0 tabular-nums">
                                {task.subtasks.filter((s) => s.is_completed).length}/{task.subtasks.length}
                            </span>
                        )}

                        <span className={`text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wide flex-shrink-0 ${
                            task.priority === 'high' ? 'text-red-500 bg-red-50'
                                : task.priority === 'medium' ? 'text-orange-500 bg-orange-50'
                                : 'text-green-600 bg-green-50'
                        }`}>
                            {task.priority}
                        </span>

                        <button
                            onClick={(e) => { e.stopPropagation(); deleteTask(task); }}
                            className="p-1 text-gray-300 hover:text-red-500 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                            title="Delete"
                        >
                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                ))}
            </div>

            {/* New Task Panel */}
            {newOpen && (
                <>
                    <div className="fixed inset-0 bg-black/30 z-40" onClick={() => setNewOpen(false)} />
                    <div className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">
                        <div className="flex items-center gap-3 px-6 py-3 border-b border-gray-200 flex-shrink-0">
                            <button onClick={() => setNewOpen(false)} className="text-gray-400 hover:text-gray-600 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div className="w-px h-4 bg-gray-200" />
                            <DatePicker value={newDate} onChange={setNewDate} iconMode label="Due Date" />
                            <div className="flex-1" />
                            <PriorityPicker value={newPriority} onChange={setNewPriority} />
                        </div>

                        <div className="flex-1 overflow-y-auto">
                            <div className="px-6 py-5">
                                <input
                                    type="text"
                                    value={newTitle}
                                    onChange={(e) => setNewTitle(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && createTask()}
                                    className="w-full text-lg font-semibold text-gray-900 outline-none ring-0 border-0 bg-transparent placeholder-gray-300"
                                    placeholder="Task title..."
                                    autoFocus
                                />
                            </div>

                            <div className="px-6 pb-4">
                                <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Project</label>
                                <select
                                    value={newProjectId}
                                    onChange={(e) => setNewProjectId(e.target.value)}
                                    className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                >
                                    <option value="">No project</option>
                                    {projects.map((p) => (
                                        <option key={p.id} value={p.id}>{p.icon || '📁'} {p.name}</option>
                                    ))}
                                </select>
                            </div>

                            {tags.length > 0 && (
                                <div className="px-6 pb-4">
                                    <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Tags</label>
                                    <div className="flex flex-wrap gap-1.5 mt-2">
                                        {tags.map((tag) => (
                                            <button
                                                key={tag.id}
                                                onClick={() => toggleNewTag(tag.id)}
                                                className={`inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full transition ${
                                                    newTagIds.includes(tag.id) ? 'text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                                }`}
                                                style={newTagIds.includes(tag.id) ? { backgroundColor: tag.color } : {}}
                                            >
                                                {tag.icon && <span className="text-xs">{tag.icon}</span>}
                                                {tag.name}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>

                        <div className="px-6 py-4 border-t border-gray-200">
                            <button
                                onClick={createTask}
                                className="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 rounded-lg transition active:scale-[0.98]"
                            >
                                Create Task
                            </button>
                        </div>
                    </div>
                </>
            )}

            {editTask && (
                <TaskEditPanel
                    task={editTask}
                    tags={tags}
                    onClose={() => setEditTask(null)}
                    onTaskUpdate={handleTaskUpdate}
                />
            )}
        </div>
    );
}
