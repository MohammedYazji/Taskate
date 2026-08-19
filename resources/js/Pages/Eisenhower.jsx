import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';

const QUADRANTS = {
    do: {
        title: 'Do First',
        subtitle: 'Urgent & Important',
        bg: 'bg-red-50',
        border: 'border-red-200',
        borderActive: 'border-red-400 bg-red-100',
        text: 'text-red-600',
        icon: '🔥',
        accent: 'bg-red-500',
    },
    schedule: {
        title: 'Schedule',
        subtitle: 'Not Urgent & Important',
        bg: 'bg-blue-50',
        border: 'border-blue-200',
        borderActive: 'border-blue-400 bg-blue-100',
        text: 'text-blue-600',
        icon: '📅',
        accent: 'bg-blue-500',
    },
    delegate: {
        title: 'Delegate',
        subtitle: 'Urgent & Not Important',
        bg: 'bg-yellow-50',
        border: 'border-yellow-200',
        borderActive: 'border-yellow-400 bg-yellow-100',
        text: 'text-yellow-600',
        icon: '🤝',
        accent: 'bg-yellow-500',
    },
    delete: {
        title: 'Eliminate',
        subtitle: 'Not Urgent & Not Important',
        bg: 'bg-gray-50',
        border: 'border-gray-200',
        borderActive: 'border-gray-400 bg-gray-100',
        text: 'text-gray-500',
        icon: '🗑️',
        accent: 'bg-gray-400',
    },
};

function TaskCard({ task }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const quadrantKeys = Object.keys(QUADRANTS);

    const handleDragStart = (e) => {
        e.dataTransfer.setData('text/plain', String(task.id));
        e.dataTransfer.effectAllowed = 'move';
        e.currentTarget.style.opacity = '0.5';
    };

    const handleDragEnd = (e) => {
        e.currentTarget.style.opacity = '1';
    };

    return (
        <div
            draggable
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
            className="bg-white rounded-xl border border-gray-200 p-3 hover:shadow-md transition group relative cursor-grab active:cursor-grabbing"
        >
            <div className="flex items-start gap-2">
                <div className="flex items-center gap-1 mt-0.5">
                    <svg className="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" />
                        <circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" />
                        <circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" />
                    </svg>
                    <div className={`w-1.5 h-1.5 rounded-full flex-shrink-0 ${
                        task.priority === 'high' ? 'bg-red-400' :
                        task.priority === 'medium' ? 'bg-yellow-400' : 'bg-green-400'
                    }`} />
                </div>
                <div className="flex-1 min-w-0">
                    <p className="text-xs font-medium text-gray-800 truncate">{task.title}</p>
                    <div className="flex items-center gap-1.5 mt-1">
                        {task.project_name && task.project_name !== 'No project' && (
                            <span className="text-[9px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{task.project_name}</span>
                        )}
                        {task.due_date && (
                            <span className="text-[9px] text-gray-400">
                                {new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                            </span>
                        )}
                        {task.subtasks?.length > 0 && (
                            <span className="text-[9px] text-gray-400">
                                {task.subtasks.filter(s => s.is_completed).length}/{task.subtasks.length}
                            </span>
                        )}
                    </div>
                </div>
                <div className="relative">
                    <button onClick={(e) => { e.stopPropagation(); setMenuOpen(!menuOpen); }}
                        className="p-1 rounded text-gray-300 hover:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                        <svg className="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="12" cy="19" r="1.5" />
                        </svg>
                    </button>
                    {menuOpen && (
                        <>
                            <div className="fixed inset-0 z-30" onClick={() => setMenuOpen(false)} />
                            <div className="absolute top-full right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-xl z-40 py-1">
                                {quadrantKeys.map(key => (
                                    <button key={key} onClick={() => { router.patch(`/tasks/${task.id}/eisenhower`, { quadrant: key }, { preserveScroll: true, preserveState: true }); setMenuOpen(false); }}
                                        className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <span>{QUADRANTS[key].icon}</span>
                                        {QUADRANTS[key].title}
                                    </button>
                                ))}
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

function QuadrantDropZone({ quadrantKey, config, tasks, moveTask }) {
    const [isOver, setIsOver] = useState(false);

    const handleDragOver = (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        setIsOver(true);
    };

    const handleDragLeave = (e) => {
        if (!e.currentTarget.contains(e.relatedTarget)) {
            setIsOver(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setIsOver(false);
        const taskId = parseInt(e.dataTransfer.getData('text/plain'));
        if (taskId) {
            moveTask(taskId, quadrantKey);
        }
    };

    return (
        <div
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
            onDrop={handleDrop}
            className={`rounded-2xl border-2 transition-all duration-200 p-4 min-h-[250px] ${
                isOver
                    ? config.borderActive
                    : `${config.border} ${config.bg}`
            }`}
        >
            <div className="flex items-center gap-2 mb-3">
                <span className="text-lg">{config.icon}</span>
                <div>
                    <h3 className={`text-sm font-semibold ${config.text}`}>{config.title}</h3>
                    <p className="text-[10px] text-gray-400">{config.subtitle}</p>
                </div>
                <span className="ml-auto text-xs font-medium text-gray-400 bg-white/60 px-2 py-0.5 rounded-full">
                    {tasks.length}
                </span>
            </div>
            <div className="space-y-2 min-h-[60px]">
                {tasks.length > 0 ? (
                    tasks.map(task => (
                        <TaskCard key={task.id} task={task} />
                    ))
                ) : (
                    <div className={`text-center py-8 rounded-xl border-2 border-dashed transition-colors ${
                        isOver ? 'border-gray-400 bg-white/40' : 'border-transparent'
                    }`}>
                        <p className="text-xs text-gray-400">
                            {isOver ? 'Drop here' : 'Drag tasks here'}
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}

export default function Eisenhower({ quadrants: initialQuadrants, tags }) {
    const [quadrants, setQuadrants] = useState(initialQuadrants);

    const moveTask = useCallback((taskId, targetQuadrant) => {
        router.patch(`/tasks/${taskId}/eisenhower`, { quadrant: targetQuadrant }, {
            preserveScroll: true,
            preserveState: true,
        });

        setQuadrants(prev => {
            let movedTask = null;
            const next = {};
            for (const [key, tasks] of Object.entries(prev)) {
                const found = tasks.find(t => t.id === taskId);
                if (found) {
                    movedTask = found;
                    next[key] = tasks.filter(t => t.id !== taskId);
                } else {
                    next[key] = [...tasks];
                }
            }
            if (movedTask) {
                next[targetQuadrant] = [...next[targetQuadrant], movedTask];
            }
            return next;
        });
    }, []);

    const totalTasks = Object.values(quadrants).reduce((sum, arr) => sum + arr.length, 0);

    return (
        <div className="max-w-7xl mx-auto select-none">
            {/* Header */}
            <div className="mb-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Eisenhower Matrix</h1>
                        <p className="text-sm text-gray-500 mt-1">{totalTasks} tasks — drag to organize</p>
                    </div>
                    <div className="flex items-center gap-3">
                        {Object.entries(QUADRANTS).map(([key, q]) => (
                            <div key={key} className="flex items-center gap-1.5 text-xs text-gray-500">
                                <div className={`w-2 h-2 rounded-full ${q.accent}`} />
                                <span>{q.title}</span>
                                <span className="text-gray-300">({quadrants[key].length})</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Matrix */}
            <div className="grid grid-cols-2 gap-4">
                {Object.entries(QUADRANTS).map(([key, config]) => (
                    <QuadrantDropZone
                        key={key}
                        quadrantKey={key}
                        config={config}
                        tasks={quadrants[key]}
                        moveTask={moveTask}
                    />
                ))}
            </div>
        </div>
    );
}
