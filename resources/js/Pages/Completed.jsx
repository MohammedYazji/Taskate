import { useState } from 'react';
import { Head, router, Link } from '@inertiajs/react';

export default function Completed({ tasks: initialTasks, projects, dateFilter, projectFilter }) {
    const [tasks, setTasks] = useState(initialTasks);
    const [editTask, setEditTask] = useState(null);

    const applyFilters = (changes) => {
        router.get(route('completed.index'), { date: dateFilter, project: projectFilter, ...changes }, { preserveState: true, preserveScroll: true, replace: true });
    };

    const toggleStatus = (task) => {
        const newStatus = task.status === 'done' ? 'todo' : 'done';
        const updated = { ...task, status: newStatus };
        setTasks((prev) => prev.map((t) => (t.id === task.id ? updated : t)));
        if (editTask?.id === task.id) setEditTask(updated);
        router.patch(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true, preserveState: true });
    };

    const dateOptions = [
        { value: 'all', label: 'All dates' },
        { value: 'week', label: 'This week' },
        { value: 'last_week', label: 'Last week' },
        { value: 'month', label: 'This month' },
    ];

    return (
        <div className="max-w-4xl">
            <Head title="Completed" />

            <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Completed</h1>
                    <p className="text-sm text-gray-500">{tasks.length} task{tasks.length !== 1 ? 's' : ''} completed</p>
                </div>
            </div>

            <div className="flex flex-wrap gap-2 mb-5">
                <div className="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden text-xs font-medium">
                    {dateOptions.map((opt, i) => (
                        <button
                            key={opt.value}
                            onClick={() => applyFilters({ date: opt.value })}
                            className={`px-3 py-1.5 transition ${i > 0 ? 'border-l border-gray-200' : ''} ${dateFilter === opt.value ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
                        >
                            {opt.label}
                        </button>
                    ))}
                </div>

                <div className="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden text-xs font-medium">
                    <button
                        onClick={() => applyFilters({ project: 'all' })}
                        className={`px-3 py-1.5 transition ${projectFilter === 'all' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
                    >
                        All lists
                    </button>
                    <button
                        onClick={() => applyFilters({ project: 'inbox' })}
                        className={`px-3 py-1.5 transition border-l border-gray-200 ${projectFilter === 'inbox' ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
                    >
                        Inbox
                    </button>
                    {projects.filter((p) => p.name !== 'Inbox').map((p) => (
                        <button
                            key={p.id}
                            onClick={() => applyFilters({ project: String(p.id) })}
                            className={`px-3 py-1.5 transition border-l border-gray-200 max-w-[120px] truncate ${projectFilter === String(p.id) ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50'}`}
                        >
                            {p.icon} {p.name}
                        </button>
                    ))}
                </div>
            </div>

            <div className="space-y-1">
                {tasks.length === 0 && (
                    <div className="text-center text-gray-400 py-12">No completed tasks found</div>
                )}
                {tasks.map((task) => (
                    <div
                        key={task.id}
                        onClick={() => setEditTask(task)}
                        className="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition cursor-pointer group"
                    >
                        <button
                            onClick={(e) => { e.stopPropagation(); toggleStatus(task); }}
                            className="w-5 h-5 rounded-[5px] border-2 flex-shrink-0 flex items-center justify-center transition"
                            style={{
                                backgroundColor: task.status === 'done' ? '#14B8A6' : task.status === 'wont_do' ? '#9CA3AF' : 'transparent',
                                borderColor: task.status === 'done' || task.status === 'wont_do' ? 'transparent' : '#D1D5DB',
                            }}
                        >
                            {task.status === 'done' && (
                                <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                </svg>
                            )}
                            {task.status === 'wont_do' && (
                                <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M18 6L6 18M6 6l12 12" />
                                </svg>
                            )}
                        </button>
                        <span className="flex-1 text-sm line-through text-gray-400">{task.title}</span>
                        {task.status === 'wont_do' && (
                            <span className="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Won't do</span>
                        )}
                        <span className={`text-[10px] font-medium px-1.5 py-0.5 rounded ${
                            task.priority === 'high' ? 'text-red-500 bg-red-50'
                                : task.priority === 'medium' ? 'text-orange-500 bg-orange-50'
                                : 'text-green-600 bg-green-50'
                        }`}>
                            {task.priority}
                        </span>
                    </div>
                ))}
            </div>

            {editTask && (
                <>
                    <div className="fixed inset-0 bg-black/30 z-40" onClick={() => setEditTask(null)} />
                    <div className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">{editTask.title}</h2>
                            <button onClick={() => setEditTask(null)} className="text-gray-400 hover:text-gray-600 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div className="p-6 space-y-4 flex-1 overflow-y-auto">
                            <div>
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Status</label>
                                <span className={`text-sm px-2 py-1 rounded-full ${editTask.status === 'done' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}`}>
                                    {editTask.status === 'done' ? 'Completed' : "Won't do"}
                                </span>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Priority</label>
                                <span className="text-sm capitalize text-gray-700">{editTask.priority}</span>
                            </div>
                            {editTask.due_date && (
                                <div>
                                    <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Due Date</label>
                                    <span className="text-sm text-gray-700">{editTask.due_date}</span>
                                </div>
                            )}
                            {editTask.project_name && (
                                <div>
                                    <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Project</label>
                                    <span className="text-sm text-gray-700">{editTask.project_name}</span>
                                </div>
                            )}
                            <div className="pt-2 border-t border-gray-100">
                                <Link href={`/dashboard?edit=${editTask.id}`} className="text-sm text-brand-500 hover:text-brand-600 font-medium transition">
                                    Open full editor
                                </Link>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
