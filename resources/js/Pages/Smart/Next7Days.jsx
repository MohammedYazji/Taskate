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
                <h1 className="text-2xl font-serif font-bold text-ink">Next 7 Days</h1>
                <p className="text-sm text-textMuted mt-1">{dateRange}</p>
            </div>

            {tasks.length === 0 && (
                <div className="text-center text-textMuted text-sm py-10">
                    No tasks due this week
                </div>
            )}

            {sortedDates.map((date) => (
                <div key={date} className="mb-6">
                    <div className="flex items-center gap-3 mb-3 px-1">
                        <h2 className={`text-xs font-semibold uppercase tracking-wide ${
                            isOverdue(date) ? 'text-terracotta' : date === todayStr ? 'text-brand-600' : 'text-textMuted'
                        }`}>
                            {formatDate(date)}
                        </h2>
                        <div className="flex-1 h-px bg-stone" />
                        <span className="text-[10px] text-textMuted">{groupedByDate[date].length}</span>
                    </div>
                    <div className="space-y-3">
                        {groupedByDate[date].map((task) => (
                            <div
                                key={task.id}
                                className={`bg-white border ${
                                    task.status === 'in_progress' ? 'border-ochre/30 shadow-tactile' : 'border-stone hover:shadow-tactile'
                                } ${task.status === 'done' ? 'opacity-60' : ''} p-4 rounded-[2rem] flex items-center gap-4 group transition-all cursor-pointer`}
                                onClick={() => openEdit(task)}
                            >
                                <button
                                    onClick={(e) => { e.stopPropagation(); toggleStatus(task); }}
                                    className={`w-6 h-6 rounded-full border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                        task.status === 'done' ? 'border-brand-500 bg-brand-50' : 
                                        'border-stone hover:border-brand-400'
                                    }`}
                                >
                                    {task.status === 'done' && (
                                        <i className="ph ph-check text-brand-600 text-xs"></i>
                                    )}
                                </button>

                                <div className="flex-1 min-w-0">
                                    <h4 className={`text-lg font-serif text-ink truncate ${task.status === 'done' ? 'line-through text-textMuted' : ''}`}>
                                        {task.title}
                                    </h4>
                                    <p className="text-xs text-textMuted mt-0.5">
                                        {task.priority && `${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}`}
                                        {task.priority && task.project_name && ' • '}
                                        {task.project_name && task.project_name}
                                        {(task.priority || task.project_name) && task.subtasks?.length > 0 && ' • '}
                                        {task.subtasks?.length > 0 && `Sub: ${task.subtasks.filter(s => s.is_completed).length}/${task.subtasks.length}`}
                                    </p>
                                </div>

                                <button
                                    onClick={(e) => { e.stopPropagation(); deleteTask(task); }}
                                    className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-terracotta hover:border-terracotta/30 transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                                    title="Delete"
                                >
                                    <i className="ph ph-trash text-sm"></i>
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
