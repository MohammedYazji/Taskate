import { useState, useEffect, useCallback, useRef } from "react";
import { router, usePage } from "@inertiajs/react";
import Sortable from "sortablejs";
import TaskEditPanel from "@/Components/TaskEditPanel";
import { DashboardSkeleton } from "@/Components/Skeleton";
import { useLoading } from "@/Components/LoadingContext";

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

export default function Dashboard({
    tasks: initialTasks,
    totalTasks,
    completedTasks,
    overdueTasks,
    tasksDueToday,
    tags,
    projects,
}) {
    const { auth } = usePage().props;
    const { loading } = useLoading();
    const [tasks, setTasks] = useState(initialTasks);

    useEffect(() => {
        setTasks(initialTasks);
    }, [initialTasks]);
    const [editTask, setEditTask] = useState(null);
    const [editOpen, setEditOpen] = useState(false);
    const [filterOpen, setFilterOpen] = useState(false);
    const [sortOpen, setSortOpen] = useState(false);
    const [filterStatus, setFilterStatus] = useState("");
    const [filterPriority, setFilterPriority] = useState("");
    const [sortBy, setSortBy] = useState("manual");

    let displayedTasks = tasks;
    if (filterStatus) {
        displayedTasks = displayedTasks.filter((t) => t.status === filterStatus);
    }
    if (filterPriority) {
        displayedTasks = displayedTasks.filter((t) => t.priority === filterPriority);
    }

    if (sortBy === "priority") {
        const order = { high: 0, medium: 1, low: 2 };
        displayedTasks = [...displayedTasks].sort(
            (a, b) => (order[a.priority] ?? 3) - (order[b.priority] ?? 3),
        );
    } else if (sortBy === "date") {
        displayedTasks = [...displayedTasks].sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return a.due_date.localeCompare(b.due_date);
        });
    } else if (sortBy === "alpha") {
        displayedTasks = [...displayedTasks].sort((a, b) =>
            a.title.localeCompare(b.title),
        );
    } else {
        displayedTasks = [...displayedTasks].sort((a, b) => {
            const aDone = a.status === "done" || a.status === "wont_do";
            const bDone = b.status === "done" || b.status === "wont_do";
            if (aDone && !bDone) return 1;
            if (!aDone && bDone) return -1;
            return (a.position ?? 999) - (b.position ?? 999);
        });
    }

    const completedTaskIds = new Set(
        displayedTasks
            .filter((t) => t.status === "done" || t.status === "wont_do")
            .map((t) => t.id),
    );

    const recurringInstances = [];
    const now = new Date();
    const rangeEnd = new Date(now);
    rangeEnd.setDate(rangeEnd.getDate() + 30);

    displayedTasks.forEach((task) => {
        if (!task.recurrence_frequency || !task.due_date) return;
        if (completedTaskIds.has(task.id)) return;

        const due = new Date(task.due_date + "T00:00:00");
        let current = new Date(due);

        while (current <= rangeEnd) {
            const ds = current.toISOString().split("T")[0];
            if (ds > task.due_date) {
                const active = task.status !== "done" && task.status !== "wont_do";
                const instance = {
                    ...task,
                    id: `${task.id}-${ds}`,
                    due_date: ds,
                    is_recurring_instance: true,
                    original_task_id: task.id,
                    title: task.title,
                    status: active ? "todo" : task.status,
                };
                recurringInstances.push(instance);
            }

            const next = new Date(current);
            if (task.recurrence_frequency === "daily") {
                next.setDate(next.getDate() + 1);
            } else if (task.recurrence_frequency === "weekly") {
                next.setDate(next.getDate() + 7);
            } else if (task.recurrence_frequency === "every-week") {
                next.setDate(next.getDate() + 7);
            } else if (task.recurrence_frequency === "monthly") {
                next.setMonth(next.getMonth() + 1);
            } else if (task.recurrence_frequency === "yearly") {
                next.setFullYear(next.getFullYear() + 1);
            } else {
                break;
            }
            current = next;
        }
    });

    displayedTasks = [...displayedTasks, ...recurringInstances];

    if (sortBy === "date") {
        displayedTasks.sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return a.due_date.localeCompare(b.due_date);
        });
    }

    const hasFilters = filterStatus || filterPriority;
    const sortLabel =
        { manual: "Manual", priority: "Priority", date: "Due date", alpha: "A-Z" }[
            sortBy
        ] || "Sort";

    const firstName = auth.user?.name?.split(" ")[0] || "";
    const hour = new Date().getHours();
    const greeting =
        hour < 12 ? "morning" : hour < 17 ? "afternoon" : "evening";

    const today = new Date();
    const calYear = today.getFullYear();
    const calMonth = today.getMonth();
    const calDaysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const calStartDow = new Date(calYear, calMonth, 1).getDay();
    const calMonthLabel = today.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
    });
    const dayNames = ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"];
    const calDays = [];
    for (let i = 0; i < (calStartDow === 0 ? 6 : calStartDow - 1); i++)
        calDays.push(null);
    for (let d = 1; d <= calDaysInMonth; d++) calDays.push(d);

    const todayStr = today.toISOString().split("T")[0];

    const taskListRef = useRef(null);

    useEffect(() => {
        if (!taskListRef.current) return;
        const el = taskListRef.current;
        const sortable = new Sortable(el, {
            animation: 150,
            ghostClass: "opacity-30",
            handle: ".drag-handle",
            onEnd: (evt) => {
                const taskId = parseInt(evt.item.dataset.id, 10);
                const newPosition = evt.newIndex;
                const order = sortable.toArray().map(Number);
                const payload = order.map((id, idx) => ({ id, position: idx }));
                fetch("/tasks/reorder", {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken(),
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({ tasks: payload }),
                });
                setTasks((prev) => {
                    const updated = [...prev];
                    const [moved] = updated.splice(evt.oldIndex, 1);
                    updated.splice(evt.newIndex, 0, moved);
                    return updated.map((t, i) => ({ ...t, position: i }));
                });
            },
        });
        return () => sortable.destroy();
    }, []);

    const openEdit = useCallback((task) => {
        setEditTask(JSON.parse(JSON.stringify(task)));
        setEditOpen(true);
    }, []);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const editId = params.get("edit");
        if (editId) {
            const task = tasks.find((t) => t.id == editId);
            if (task) openEdit(task);
            window.history.replaceState(null, "", window.location.pathname);
        }
    }, []);

    useEffect(() => {
        if (editOpen && editTask) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
        return () => {
            document.body.style.overflow = "";
        };
    }, [editOpen]);

    const handleTaskUpdate = useCallback((updated) => {
        setTasks((prev) => {
            const exists = prev.some((t) => t.id === updated.id);
            if (exists) {
                return prev.map((t) => (t.id === updated.id ? updated : t));
            }
            return [...prev, updated];
        });
    }, []);

    const deleteTask = useCallback(
        (task) => {
            if (!confirm("Are you sure you want to delete this task?")) return;
            const realId = task.original_task_id || task.id;
            router.delete(`/tasks/${realId}`, {
                preserveScroll: true,
                preserveState: true,
            });
            setTasks((prev) => prev.filter((t) => t.id !== realId));
            if (editOpen && editTask?.id === realId) setEditOpen(false);
        },
        [editOpen, editTask],
    );

    const toggleStatus = useCallback(
        (task) => {
            if (task.is_recurring_instance) {
                const realId = task.original_task_id;
                setTasks((prev) =>
                    prev.map((t) => (t.id === realId ? { ...t, status: t.status === "done" ? "todo" : "done" } : t)),
                );
                return;
            }
            const newStatus = task.status === "done" ? "todo" : "done";
            const updated = { ...task, status: newStatus };
            setTasks((prev) =>
                prev.map((t) => (t.id === task.id ? updated : t)),
            );
            if (editOpen && editTask?.id === task.id) setEditTask(updated);
            fetch(`/tasks/${task.id}/toggle`, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": csrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
        },
        [editOpen, editTask],
    );

    const completionRate =
        totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

    const PRIORITY_COLORS = {
        low: "text-green-500",
        medium: "text-yellow-500",
        high: "text-red-500",
    };
    const PRIORITY_BG = {
        low: "bg-green-100 text-green-700",
        medium: "bg-yellow-100 text-yellow-700",
        high: "bg-red-100 text-red-700",
    };

    const getGreetingEmoji = () => {
        if (hour < 12) return "🌅";
        if (hour < 17) return "☀️";
        return "🌙";
    };

    if (loading) return <DashboardSkeleton />;

    return (
        <div className="flex flex-col lg:flex-row gap-6">
            {/* Left Column */}
            <div className="flex-1 min-w-0">
                {/* Search Bar */}
                <form method="GET" action={route("search")} className="mb-6">
                    <div className="flex items-center gap-2 bg-white border border-stone rounded-2xl px-4 py-2.5 w-full max-w-md">
                        <i className="ph ph-magnifying-glass text-textMuted text-sm"></i>
                        <input
                            type="text"
                            name="q"
                            placeholder="Search tasks, projects..."
                            className="bg-transparent text-sm text-textMain outline-none ring-0 border-0 focus:ring-0 focus:outline-none focus:border-0 focus:shadow-none w-full placeholder-textMuted"
                        />
                    </div>
                </form>

                {/* Greeting */}
                <div className="mb-8">
                    <h1 className="text-3xl font-serif font-bold text-ink">
                        Good {greeting}, {firstName} {getGreetingEmoji()}
                    </h1>
                    <p className="text-sm text-textMuted mt-1">
                        {today.toLocaleDateString("en-US", {
                            weekday: "long",
                            month: "long",
                            day: "numeric",
                        })}{" "}
                        ·{" "}
                        {tasksDueToday > 0
                            ? `You have ${tasksDueToday} task${tasksDueToday > 1 ? "s" : ""} due today`
                            : "No tasks due today"}
                    </p>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div className="bg-white rounded-[2rem] border border-stone p-6 hover:shadow-tactile transition-shadow">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm text-textMuted font-serif">
                                Total Tasks
                            </span>
                            <div className="w-10 h-10 rounded-full border border-stone bg-paper flex items-center justify-center">
                                <i className="ph ph-list-checks text-ochre text-lg"></i>
                            </div>
                        </div>
                        <p className="text-4xl font-serif font-bold text-ink">
                            {totalTasks}
                        </p>
                    </div>

                    <div className="bg-white rounded-[2rem] border border-stone p-6 hover:shadow-tactile transition-shadow">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm text-textMuted font-serif">
                                Completed
                            </span>
                            <div className="w-10 h-10 rounded-full border border-stone bg-paper flex items-center justify-center">
                                <i className="ph ph-check-circle text-brand-600 text-lg"></i>
                            </div>
                        </div>
                        <p className="text-4xl font-serif font-bold text-ink">
                            {completedTasks}
                        </p>
                        <p className="text-xs text-textMuted mt-1 font-serif">
                            {completionRate}% completion rate
                        </p>
                    </div>

                    <div className="bg-white rounded-[2rem] border border-stone p-6 hover:shadow-tactile transition-shadow">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm text-textMuted font-serif">
                                Overdue
                            </span>
                            <div className="w-10 h-10 rounded-full border border-stone bg-paper flex items-center justify-center">
                                <i className="ph ph-clock text-terracotta text-lg"></i>
                            </div>
                        </div>
                        <p
                            className={`text-4xl font-serif font-bold ${overdueTasks > 0 ? "text-terracotta" : "text-ink"}`}
                        >
                            {overdueTasks}
                        </p>
                        <p className="text-xs text-textMuted mt-1 font-serif">
                            {overdueTasks > 0
                                ? `${overdueTasks} high priority`
                                : "All caught up!"}
                        </p>
                    </div>
                </div>

                {/* My Tasks */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between px-1">
                        <h2 className="font-semibold text-ink font-serif text-xl">
                            My Tasks
                        </h2>
                        <div className="flex items-center gap-2">
                            <div className="relative">
                                <button
                                    onClick={() => { setFilterOpen(!filterOpen); setSortOpen(false); }}
                                    className="text-xs text-textMuted border border-stone px-3 py-1.5 rounded-xl hover:bg-stone/20 transition flex items-center gap-1.5"
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
                                        <div className="absolute top-full left-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating p-3 min-w-48 z-20">
                                            <div className="space-y-2">
                                                <div>
                                                    <label className="block text-xs font-medium text-textMuted mb-1">Status</label>
                                                    <select
                                                        value={filterStatus}
                                                        onChange={(e) => setFilterStatus(e.target.value)}
                                                        className="w-full text-xs border border-stone rounded-xl px-2 py-1 outline-none focus:ring-1 focus:ring-ochre"
                                                    >
                                                        <option value="">All</option>
                                                        <option value="todo">Todo</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="done">Done</option>
                                                        <option value="wont_do">Won't Do</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-textMuted mb-1">Priority</label>
                                                    <select
                                                        value={filterPriority}
                                                        onChange={(e) => setFilterPriority(e.target.value)}
                                                        className="w-full text-xs border border-stone rounded-xl px-2 py-1 outline-none focus:ring-1 focus:ring-ochre"
                                                    >
                                                        <option value="">All</option>
                                                        <option value="high">High</option>
                                                        <option value="medium">Medium</option>
                                                        <option value="low">Low</option>
                                                    </select>
                                                </div>
                                                {hasFilters && (
                                                    <button
                                                        onClick={() => { setFilterStatus(""); setFilterPriority(""); }}
                                                        className="block text-xs text-brand-600 hover:text-brand-500 mt-2"
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
                                    className="text-xs text-textMuted border border-stone px-3 py-1.5 rounded-xl hover:bg-stone/20 transition flex items-center gap-1.5"
                                >
                                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7h6M3 12h12M3 17h8" />
                                    </svg>
                                    {sortLabel}
                                    {sortBy !== "manual" && <span className="w-1.5 h-1.5 bg-brand-500 rounded-full" />}
                                </button>
                                {sortOpen && (
                                    <>
                                        <div className="fixed inset-0 z-10" onClick={() => setSortOpen(false)} />
                                        <div className="absolute top-full left-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating p-3 min-w-48 z-20">
                                            <div className="space-y-1">
                                                {[
                                                    { value: "manual", label: "Manual" },
                                                    { value: "priority", label: "Priority" },
                                                    { value: "date", label: "Due date" },
                                                    { value: "alpha", label: "A-Z" },
                                                ].map((opt) => (
                                                    <label key={opt.value} className="flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-stone/20 cursor-pointer">
                                                        <input
                                                            type="radio"
                                                            name="sort"
                                                            checked={sortBy === opt.value}
                                                            onChange={() => { setSortBy(opt.value); setSortOpen(false); }}
                                                            className="text-ochre focus:ring-ochre"
                                                        />
                                                        <span className="text-xs text-textMain">{opt.label}</span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>

                    <div ref={taskListRef} className="space-y-3">
                        {displayedTasks.map((task, index) => (
                            <div
                                key={task.id}
                                data-id={typeof task.id === 'number' ? task.id : task.original_task_id}
                                className={`bg-white border ${
                                    task.status === 'in_progress' ? 'border-ochre/30 shadow-tactile' : 'border-stone hover:shadow-tactile'
                                } ${task.status === 'done' || task.status === 'wont_do' ? 'opacity-60' : ''} p-4 rounded-[2rem] flex items-center gap-4 group transition-all cursor-pointer`}
                                onClick={() => openEdit(task)}
                            >
                                <button
                                    onClick={(e) => e.stopPropagation()}
                                    className={`drag-handle cursor-grab active:cursor-grabbing text-stone hover:text-ink rounded transition flex-shrink-0 ${task.is_recurring_instance ? 'opacity-0 pointer-events-none' : 'opacity-0 group-hover:opacity-100'}`}
                                    title="Drag to reorder"
                                >
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
                                </button>
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        toggleStatus(task);
                                    }}
                                    className={`w-6 h-6 rounded-full border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                        task.status === 'done' ? 'border-brand-500 bg-brand-50' : 
                                        task.status === 'wont_do' ? 'border-stone bg-stone/20' : 
                                        'border-stone hover:border-brand-400'
                                    }`}
                                >
                                    {task.status === 'done' && (
                                        <i className="ph ph-check text-brand-600 text-xs"></i>
                                    )}
                                    {task.status === 'wont_do' && (
                                        <i className="ph ph-x text-textMuted text-xs"></i>
                                    )}
                                </button>

                                <div className="flex-1 min-w-0">
                                    <h4 className={`text-lg font-serif text-ink truncate flex items-center gap-2 ${task.status === 'done' || task.status === 'wont_do' ? 'line-through text-textMuted' : ''}`}>
                                        {task.is_recurring_instance && (
                                            <i className="ph ph-arrows-clockwise text-ochre text-sm flex-shrink-0" title="Recurring"></i>
                                        )}
                                        {task.recurrence_frequency && !task.is_recurring_instance && (
                                            <i className="ph ph-arrows-clockwise text-ochre text-sm flex-shrink-0" title="Recurring"></i>
                                        )}
                                        {task.title}
                                    </h4>
                                    <p className="text-xs text-textMuted mt-0.5">
                                        {task.priority && `${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}`}
                                        {task.priority && task.due_date && ' • '}
                                        {task.due_date && (
                                            task.due_date === todayStr ? 'Today' : new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                                        )}
                                        {(task.priority || task.due_date) && task.project_name && task.project_name !== 'No project' && ' • '}
                                        {task.project_name && task.project_name !== 'No project' && task.project_name}
                                        {((task.priority || task.due_date || (task.project_name && task.project_name !== 'No project'))) && task.subtasks?.length > 0 && ' • '}
                                        {task.subtasks?.length > 0 && `Sub: ${task.subtasks.filter(s => s.is_completed).length}/${task.subtasks.length}`}
                                    </p>
                                </div>

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        deleteTask(task);
                                    }}                                    
                                    className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-terracotta hover:border-terracotta/30 transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                                    title="Delete"
                                >
                                    <i className="ph ph-trash text-sm"></i>
                                </button>
                            </div>
                        ))}

                        {displayedTasks.length === 0 && (
                            <div className="text-center text-textMuted text-sm py-10">
                                No tasks yet — create your first one!
                            </div>
                        )}
                    </div>
                </div>

            </div>

            {/* Right Column */}
            <div className="w-72 flex-shrink-0 space-y-4 hidden xl:block">
                {/* Calendar */}
                <div className="bg-white rounded-[2rem] border border-stone p-5">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-serif font-semibold text-ink">
                            {calMonthLabel}
                        </h3>
                    </div>
                    <div className="grid grid-cols-7 mb-2">
                        {dayNames.map((d) => (
                            <div
                                key={d}
                                className="text-center text-xs text-textMuted py-1"
                            >
                                {d}
                            </div>
                        ))}
                    </div>
                    <div className="grid grid-cols-7 gap-y-1">
                        {calDays.map((day, idx) => (
                            <div
                                key={idx}
                                className="flex items-center justify-center"
                            >
                                {day ? (
                                    <a
                                        href={route("tasks.index", {
                                            date: `${calYear}-${String(calMonth + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`,
                                        })}
                                        className={`w-8 h-8 flex items-center justify-center text-xs rounded-full transition ${
                                            day === today.getDate()
                                                ? "bg-brand-500 text-paper font-semibold"
                                                : "text-textMain hover:bg-stone/20"
                                        }`}
                                    >
                                        {day}
                                    </a>
                                ) : (
                                    <div></div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Upcoming */}
                <div className="bg-white rounded-[2rem] border border-stone p-5">
                    <h3 className="font-serif font-semibold text-ink mb-3">
                        Upcoming
                    </h3>
                    <div className="space-y-2">
                        {tasks
                            .filter(
                                (t) =>
                                    t.due_date &&
                                    t.due_date >= todayStr &&
                                    t.status !== "done",
                            )
                            .sort((a, b) =>
                                a.due_date.localeCompare(b.due_date),
                            )
                            .slice(0, 4)
                            .map((task) => (
                                <div
                                    key={task.id}
                                    className="flex items-start gap-3 cursor-pointer rounded-2xl p-2 -mx-2 transition hover:bg-stone/20"
                                    onClick={() => openEdit(task)}
                                >
                                    <div className="w-1 h-8 rounded-full bg-brand-500 flex-shrink-0 mt-0.5"></div>
                                    <div className="min-w-0">
                                        <p className="text-xs font-medium text-ink truncate">
                                            {task.title}
                                        </p>
                                        <p className="text-[10px] text-textMuted mt-0.5">
                                            {task.due_date === todayStr
                                                ? "Today"
                                                : new Date(
                                                      task.due_date +
                                                          "T00:00:00",
                                                  ).toLocaleDateString(
                                                      "en-US",
                                                      {
                                                          month: "short",
                                                          day: "numeric",
                                                      },
                                                  )}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        {tasks.filter(
                            (t) =>
                                t.due_date &&
                                t.due_date >= todayStr &&
                                t.status !== "done",
                        ).length === 0 && (
                            <p className="text-xs text-textMuted">
                                No upcoming tasks
                            </p>
                        )}
                    </div>
                </div>
            </div>

            {/* New Task FAB */}
            <button
                onClick={() => {
                    setEditTask({
                        title: "",
                        description: "",
                        priority: "medium",
                        status: "todo",
                        due_date: null,
                        is_recurring: false,
                        recurrence_frequency: null,
                        tag_ids: [],
                        project_id: null,
                        subtasks: [],
                        comments: [],
                    });
                    setEditOpen(true);
                }}
                className="fixed bottom-6 right-6 w-14 h-14 bg-brand-500 hover:bg-brand-600 text-paper rounded-full shadow-floating hover:shadow-tactile flex items-center justify-center transition z-30 active:scale-95"
            >
                <i className="ph ph-plus ph-xl"></i>
            </button>

            {/* === EDIT TASK PANEL === */}
            {editOpen && editTask && (
                <TaskEditPanel
                    task={editTask}
                    tags={tags}
                    onClose={() => setEditOpen(false)}
                    onTaskUpdate={handleTaskUpdate}
                />
            )}

            <style>{`
                @keyframes slideIn {
                    from { transform: translateX(100%); }
                    to { transform: translateX(0); }
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
            `}</style>
        </div>
    );
}
