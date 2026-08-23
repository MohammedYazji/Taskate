import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import TaskEditPanel from '@/Components/TaskEditPanel';

const PRIORITY_COLORS = {
    low: 'text-green-500',
    medium: 'text-yellow-500',
    high: 'text-red-500',
};

export default function Next7Days({ tasks: initialTasks, tags, dateRange }) {
    const [tasks, setTasks] = useState(initialTasks);
    const [editTask, setEditTask] = useState(null);

    const toggleStatus = useCallback((task) => {
        const newStatus = task.status === 'done' ? 'todo' : 'done';
        const updated = { ...task, status: newStatus };
        setTasks(prev => prev.map(t => t.id === task.id ? updated : t));
        if (editTask?.id === task.id) setEditTask(updated);
        router.patch(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true, preserveState: true });
    }, [editTask]);

    const deleteTask = useCallback((task) => {
        if (!confirm('Are you sure you want to delete this task?')) return;
        router.delete(`/tasks/${task.id}`, { preserveScroll: true, preserveState: true });
        setTasks(prev => prev.filter(t => t.id !== task.id));
        if (editTask?.id === task.id) setEditTask(null);
    }, [editTask]);

    const openEdit = useCallback((task) => {
        setEditTask(JSON.parse(JSON.stringify(task)));
    }, []);

    const handleTaskUpdate = useCallback((updated) => {
        setTasks(prev => prev.map(t => t.id === updated.id ? updated : t));
    }, []);

    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];

    const groupedByDate = tasks.reduce((acc, task) => {
        const key = task.due_date || 'No date';
        if (!acc[key]) acc[key] = [];
        acc[key].push(task);
        return acc;
    }, {});

    const sortedDates = Object.keys(groupedByDate).sort();

    const formatDate = (dateStr) => {
        if (dateStr === 'No date') return 'No date';
        const d = new Date(dateStr + 'T00:00:00');
        const isToday = dateStr === todayStr;
        const dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
        const monthDay = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        return isToday ? `Today · ${monthDay}` : `${dayName} · ${monthDay}`;
    };

    const isOverdue = (dateStr) => {
        return dateStr && new Date(dateStr + 'T23:59:59') < new Date();
    };

    return (
        <div className="max-w-4xl mx-auto px-6 py-8">
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900">Next 7 Days</h1>
                <p className="text-sm text-gray-500 mt-1">{dateRange}</p>
            </div>

            {tasks.length === 0 && (
                <div className="bg-white rounded-xl border border-gray-200 px-5 py-10 text-center text-gray-400 text-sm">
                    No tasks due this week
                </div>
            )}

            {sortedDates.map((date) => (
                <div key={date} className="mb-6">
                    <div className="flex items-center gap-3 mb-2 px-1">
                        <h2 className={`text-xs font-semibold uppercase tracking-wide ${
                            isOverdue(date) ? 'text-red-500' : date === todayStr ? 'text-brand-500' : 'text-gray-500'
                        }`}>
                            {formatDate(date)}
                        </h2>
                        <div className="flex-1 h-px bg-gray-100" />
                        <span className="text-[10px] text-gray-400">{groupedByDate[date].length}</span>
                    </div>
                    <div className="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                        {groupedByDate[date].map((task) => (
                            <div
                                key={task.id}
                                className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 transition group cursor-pointer"
                                onClick={() => openEdit(task)}
                            >
                                <button
                                    onClick={(e) => { e.stopPropagation(); toggleStatus(task); }}
                                    className={`w-[18px] h-[18px] rounded-[5px] border-[1.5px] flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                        task.status === 'done' ? 'bg-brand-500 border-brand-500' : 'border-gray-300 hover:border-brand-400'
                                    }`}
                                >
                                    {task.status === 'done' && (
                                        <svg className="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    )}
                                </button>

                                <span className={`flex-1 min-w-0 text-sm truncate transition ${
                                    task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'
                                }`}>
                                    {task.title}
                                </span>

                                {task.project_name && (
                                    <span className="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0">
                                        {task.project_name}
                                    </span>
                                )}

                                {task.subtasks?.length > 0 && (
                                    <span className="text-[10px] text-gray-400 flex-shrink-0 tabular-nums">
                                        {task.subtasks.filter(s => s.is_completed).length}/{task.subtasks.length}
                                    </span>
                                )}

                                {task.priority && task.priority !== 'medium' && (
                                    <span className={`flex-shrink-0 text-[10px] font-medium capitalize ${PRIORITY_COLORS[task.priority] || ''}`}>
                                        {task.priority === 'high' ? '!!!' : task.priority === 'low' ? '!' : ''}
                                    </span>
                                )}

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
                </div>
            ))}

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
