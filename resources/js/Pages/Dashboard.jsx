import { useState, useEffect, useCallback, useRef } from "react";
import { router, usePage } from "@inertiajs/react";
import DatePicker from "@/Components/DatePicker";
import TiptapEditor from "@/Components/TiptapEditor";
import PriorityPicker from "@/Components/PriorityPicker";

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
    const [tasks, setTasks] = useState(initialTasks);
    const [editOpen, setEditOpen] = useState(false);
    const [editTask, setEditTask] = useState(null);
    const [newOpen, setNewOpen] = useState(false);
    const [newTitle, setNewTitle] = useState("");
    const [newPriority, setNewPriority] = useState("medium");
    const [newDate, setNewDate] = useState("");
    const [newProjectId, setNewProjectId] = useState("");
    const [newTagIds, setNewTagIds] = useState([]);
    const [editMenuOpen, setEditMenuOpen] = useState(false);
    const [commentsOpen, setCommentsOpen] = useState(false);
    const [descHtml, setDescHtml] = useState("");
    const [newSubtaskTitle, setNewSubtaskTitle] = useState("");
    const [editingNewSubtask, setEditingNewSubtask] = useState(false);
    const [commentBody, setCommentBody] = useState("");
    const saveTimerRef = useRef(null);
    const descTimerRef = useRef(null);

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

    const openEdit = useCallback((task) => {
        setEditTask(JSON.parse(JSON.stringify(task)));
        setEditOpen(true);
        setCommentsOpen(false);
        setEditMenuOpen(false);
        setDescHtml(task.description || "");
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

    const syncTask = useCallback(() => {
        setTasks((prev) =>
            prev.map((t) => (t.id === editTask.id ? { ...editTask } : t)),
        );
    }, [editTask]);

    const saveField = useCallback(
        (field, value) => {
            const updated = { ...editTask, [field]: value };
            setEditTask(updated);
            setTasks((prev) =>
                prev.map((t) => (t.id === updated.id ? updated : t)),
            );
            clearTimeout(saveTimerRef.current);
            saveTimerRef.current = setTimeout(() => {
                router.patch(
                    `/tasks/${updated.id}`,
                    { [field]: value },
                    { preserveScroll: true, preserveState: true },
                );
            }, 500);
        },
        [editTask],
    );

    const deleteTask = useCallback(
        (index) => {
            if (!confirm("Are you sure you want to delete this task?")) return;
            const task = tasks[index];
            router.delete(`/tasks/${task.id}`, {
                preserveScroll: true,
                preserveState: true,
            });
            setTasks((prev) => prev.filter((_, i) => i !== index));
            if (editOpen && editTask?.id === task.id) setEditOpen(false);
        },
        [tasks, editOpen, editTask],
    );

    const toggleStatus = useCallback(
        (task) => {
            const newStatus = task.status === "done" ? "todo" : "done";
            const updated = { ...task, status: newStatus };
            setTasks((prev) =>
                prev.map((t) => (t.id === task.id ? updated : t)),
            );
            if (editOpen && editTask?.id === task.id) setEditTask(updated);
            router.patch(
                `/tasks/${task.id}/toggle`,
                {},
                { preserveScroll: true, preserveState: true },
            );
        },
        [editOpen, editTask],
    );

    const createTask = () => {
        if (!newTitle.trim()) return;
        router.post(
            "/tasks",
            {
                title: newTitle,
                priority: newPriority,
                due_date: newDate || null,
                project_id: newProjectId || null,
                tag_ids: newTagIds,
                is_recurring: false,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setNewOpen(false);
                    setNewTitle("");
                    setNewPriority("medium");
                    setNewDate("");
                    setNewProjectId("");
                    setNewTagIds([]);
                },
            },
        );
    };

    const toggleNewTag = (tagId) => {
        setNewTagIds((prev) =>
            prev.includes(tagId)
                ? prev.filter((id) => id !== tagId)
                : [...prev, tagId],
        );
    };

    const toggleEditTag = (tagId) => {
        if (!editTask) return;
        const newIds = editTask.tag_ids.includes(tagId)
            ? editTask.tag_ids.filter((id) => id !== tagId)
            : [...editTask.tag_ids, tagId];
        const updated = { ...editTask, tag_ids: newIds };
        setEditTask(updated);
        setTasks((prev) =>
            prev.map((t) => (t.id === updated.id ? updated : t)),
        );
        clearTimeout(saveTimerRef.current);
        saveTimerRef.current = setTimeout(() => {
            router.patch(
                `/tasks/${editTask.id}`,
                { tag_ids: newIds },
                { preserveScroll: true, preserveState: true },
            );
        }, 500);
    };

    const addSubtask = () => {
        if (!newSubtaskTitle.trim() || !editTask) return;
        router.post(
            `/tasks/${editTask.id}/subtasks`,
            { title: newSubtaskTitle },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setNewSubtaskTitle("");
                    setEditingNewSubtask(false);
                },
            },
        );
    };

    const toggleSubtask = (subtask) => {
        router.patch(
            `/subtasks/${subtask.id}/toggle`,
            {},
            { preserveScroll: true, preserveState: true },
        );
        const updated = {
            ...editTask,
            subtasks: editTask.subtasks.map((s) =>
                s.id === subtask.id
                    ? { ...s, is_completed: !s.is_completed }
                    : s,
            ),
        };
        setEditTask(updated);
        setTasks((prev) =>
            prev.map((t) => (t.id === updated.id ? updated : t)),
        );
    };

    const deleteSubtask = (subtask) => {
        router.delete(`/subtasks/${subtask.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
        const updated = {
            ...editTask,
            subtasks: editTask.subtasks.filter((s) => s.id !== subtask.id),
        };
        setEditTask(updated);
        setTasks((prev) =>
            prev.map((t) => (t.id === updated.id ? updated : t)),
        );
    };

    const submitComment = (e) => {
        e.preventDefault();
        if (!commentBody.trim() || !editTask) return;
        router.post(
            `/tasks/${editTask.id}/comments`,
            { body: commentBody },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setEditTask({
                        ...editTask,
                        comments: [
                            ...(editTask.comments || []),
                            {
                                id: Date.now(),
                                body: commentBody,
                                user_name: auth.user.name,
                                created_at: "just now",
                            },
                        ],
                    });
                    setCommentBody("");
                },
            },
        );
    };

    const saveDescription = (value) => {
        setDescHtml(value);
        clearTimeout(descTimerRef.current);
        descTimerRef.current = setTimeout(() => {
            if (editTask) {
                router.patch(
                    `/tasks/${editTask.id}/description`,
                    { description: value },
                    { preserveScroll: true, preserveState: true },
                );
            }
        }, 500);
    };

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

    return (
        <div className="flex gap-6">
            {/* Left Column */}
            <div className="flex-1 min-w-0">
                {/* Search Bar */}
                <form method="GET" action={route("search")} className="mb-6">
                    <div className="flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2.5 w-full max-w-md">
                        <svg
                            className="w-4 h-4 text-gray-400 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            type="text"
                            name="q"
                            placeholder="Search tasks, projects..."
                            className="bg-transparent text-sm text-gray-600 outline-none ring-0 border-0 focus:ring-0 focus:outline-none focus:border-0 focus:shadow-none w-full placeholder-gray-400"
                        />
                    </div>
                </form>

                {/* Greeting */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Good {greeting}, {firstName} {getGreetingEmoji()}
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {today.toLocaleDateString("en-US", {
                            weekday: "long",
                            month: "long",
                            day: "numeric",
                        })}{" "}
                        ·{" "}
                        {tasksDueToday > 0
                            ? `You have ${tasksDueToday} task${tasksDueToday > 1 ? "s" : ""} due today`
                            : "No tasks due today 🎉"}
                    </p>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-3 gap-4 mb-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                        <div className="flex items-center justify-between mb-3">
                            <span className="text-sm text-gray-500">
                                Total Tasks
                            </span>
                            <div className="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg
                                    className="w-4 h-4 text-blue-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                    />
                                </svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-gray-900">
                            {totalTasks}
                        </p>
                    </div>

                    <div className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                        <div className="flex items-center justify-between mb-3">
                            <span className="text-sm text-gray-500">
                                Completed
                            </span>
                            <div className="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                <svg
                                    className="w-4 h-4 text-green-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-gray-900">
                            {completedTasks}
                        </p>
                        <p className="text-xs text-gray-400 mt-1">
                            {completionRate}% completion rate
                        </p>
                    </div>

                    <div className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                        <div className="flex items-center justify-between mb-3">
                            <span className="text-sm text-gray-500">
                                Overdue
                            </span>
                            <div className="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                                <svg
                                    className="w-4 h-4 text-red-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <p
                            className={`text-3xl font-bold ${overdueTasks > 0 ? "text-red-600" : "text-gray-900"}`}
                        >
                            {overdueTasks}
                        </p>
                        <p className="text-xs text-gray-400 mt-1">
                            {overdueTasks > 0
                                ? `${overdueTasks} high priority`
                                : "All caught up!"}
                        </p>
                    </div>
                </div>

                {/* My Tasks */}
                <div className="bg-white rounded-xl border border-gray-200">
                    <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 className="font-semibold text-gray-900">
                            My Tasks
                        </h2>
                        <div className="flex items-center gap-2">
                            <button className="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                                Filter
                            </button>
                            <button className="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                                Sort
                            </button>
                        </div>
                    </div>

                    <div className="divide-y divide-gray-100">
                        {tasks.map((task, index) => (
                            <div
                                key={task.id}
                                className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 transition group cursor-pointer"
                                onClick={() => openEdit(task)}
                            >
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        toggleStatus(task);
                                    }}
                                    className={`w-[18px] h-[18px] rounded-[5px] border-[1.5px] flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                        task.status === "done"
                                            ? "bg-brand-500 border-brand-500"
                                            : task.status === "wont_do"
                                              ? "bg-red-500 border-red-500"
                                              : "border-gray-300 hover:border-brand-400"
                                    }`}
                                >
                                    {task.status === "done" && (
                                        <svg
                                            className="w-2.5 h-2.5 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="3"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    )}
                                    {task.status === "wont_do" && (
                                        <svg
                                            className="w-2.5 h-2.5 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="3"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    )}
                                </button>

                                <span
                                    className={`flex-1 min-w-0 text-sm truncate transition ${
                                        task.status === "done"
                                            ? "line-through text-gray-400"
                                            : task.status === "wont_do"
                                              ? "line-through text-red-400"
                                              : "text-gray-800"
                                    }`}
                                >
                                    {task.title}
                                </span>

                                {task.project_name &&
                                    task.project_name !== "No project" && (
                                        <span className="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0">
                                            {task.project_name}
                                        </span>
                                    )}

                                {task.due_date && (
                                    <span
                                        className={`text-[10px] flex-shrink-0 px-1.5 py-0.5 rounded ${
                                            task.status === "done"
                                                ? "text-gray-400"
                                                : new Date(
                                                        task.due_date +
                                                            "T23:59:59",
                                                    ) < new Date()
                                                  ? "bg-red-50 text-red-500 font-medium"
                                                  : task.due_date === todayStr
                                                    ? "bg-brand-50 text-brand-600 font-medium"
                                                    : "text-gray-400"
                                        }`}
                                    >
                                        {task.due_date === todayStr
                                            ? "Today"
                                            : new Date(
                                                  task.due_date + "T00:00:00",
                                              ).toLocaleDateString("en-US", {
                                                  month: "short",
                                                  day: "numeric",
                                              })}
                                    </span>
                                )}

                                {task.subtasks?.length > 0 && (
                                    <span className="text-[10px] text-gray-400 flex-shrink-0 tabular-nums">
                                        {
                                            task.subtasks.filter(
                                                (s) => s.is_completed,
                                            ).length
                                        }
                                        /{task.subtasks.length}
                                    </span>
                                )}

                                {task.priority &&
                                    task.priority !== "medium" && (
                                        <span
                                            className={`flex-shrink-0 text-[10px] font-medium capitalize ${PRIORITY_COLORS[task.priority] || ""}`}
                                        >
                                            {task.priority === "high"
                                                ? "!!!"
                                                : task.priority === "low"
                                                  ? "!"
                                                  : ""}
                                        </span>
                                    )}

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        deleteTask(index);
                                    }}
                                    className="p-1 text-gray-300 hover:text-red-500 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                                    title="Delete"
                                >
                                    <svg
                                        className="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        ))}

                        {tasks.length === 0 && (
                            <div className="px-5 py-10 text-center text-gray-400 text-sm">
                                No tasks yet — create your first one!
                            </div>
                        )}
                    </div>
                </div>

                {/* New Task FAB */}
                <button
                    onClick={() => setNewOpen(true)}
                    className="fixed bottom-6 right-6 w-14 h-14 bg-brand-500 hover:bg-brand-600 text-white rounded-full shadow-lg hover:shadow-xl flex items-center justify-center transition z-30 active:scale-95"
                >
                    <svg
                        className="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                </button>
            </div>

            {/* Right Column */}
            <div className="w-72 flex-shrink-0 space-y-4">
                {/* Calendar */}
                <div className="bg-white rounded-xl border border-gray-200 p-4">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-semibold text-gray-900">
                            {calMonthLabel}
                        </h3>
                    </div>
                    <div className="grid grid-cols-7 mb-2">
                        {dayNames.map((d) => (
                            <div
                                key={d}
                                className="text-center text-xs text-gray-400 py-1"
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
                                        className={`w-7 h-7 flex items-center justify-center text-xs rounded-full transition ${
                                            day === today.getDate()
                                                ? "bg-brand-500 text-white font-semibold"
                                                : "text-gray-600 hover:bg-gray-100"
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
                <div className="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 className="font-semibold text-gray-900 mb-3">
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
                                    className="flex items-start gap-2 cursor-pointer hover:bg-gray-50 rounded-lg p-1 -m-1 transition"
                                    onClick={() => openEdit(task)}
                                >
                                    <div className="w-1 h-8 rounded-full bg-brand-500 flex-shrink-0 mt-0.5"></div>
                                    <div className="min-w-0">
                                        <p className="text-xs font-medium text-gray-800 truncate">
                                            {task.title}
                                        </p>
                                        <p className="text-[10px] text-gray-400 mt-0.5">
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
                            <p className="text-xs text-gray-400">
                                No upcoming tasks
                            </p>
                        )}
                    </div>
                </div>
            </div>

            {/* === NEW TASK PANEL === */}
            {newOpen && (
                <>
                    <div
                        className="fixed inset-0 bg-black/30 z-40"
                        onClick={() => setNewOpen(false)}
                        style={{ animation: "fadeIn 0.2s ease-out" }}
                    ></div>
                    <div
                        className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
                        style={{ animation: "slideIn 0.3s ease-out" }}
                    >
                        <div className="flex items-center gap-3 px-6 py-3 border-b border-gray-200 flex-shrink-0">
                            <button
                                onClick={() => setNewOpen(false)}
                                className="text-gray-400 hover:text-gray-600 transition"
                            >
                                <svg
                                    className="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                            <div className="w-px h-4 bg-gray-200"></div>
                            <DatePicker
                                value={newDate}
                                onChange={setNewDate}
                                iconMode
                                label="Due Date"
                            />
                            <div className="flex-1"></div>
                            <PriorityPicker
                                value={newPriority}
                                onChange={setNewPriority}
                            />
                        </div>

                        <div className="flex-1 overflow-y-auto">
                            <div className="px-6 py-5">
                                <input
                                    type="text"
                                    value={newTitle}
                                    onChange={(e) =>
                                        setNewTitle(e.target.value)
                                    }
                                    onKeyDown={(e) =>
                                        e.key === "Enter" && createTask()
                                    }
                                    className="w-full text-lg font-semibold text-gray-900 outline-none ring-0 border-0 bg-transparent placeholder-gray-300"
                                    placeholder="Task title..."
                                    autoFocus
                                />
                            </div>

                            {/* Project */}
                            <div className="px-6 pb-4">
                                <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Project
                                </label>
                                <select
                                    value={newProjectId}
                                    onChange={(e) =>
                                        setNewProjectId(e.target.value)
                                    }
                                    className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                >
                                    <option value="">No project</option>
                                    {projects.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.icon || "📁"} {p.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Tags */}
                            {tags.length > 0 && (
                                <div className="px-6 pb-4">
                                    <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                        Tags
                                    </label>
                                    <div className="flex flex-wrap gap-1.5 mt-2">
                                        {tags.map((tag) => (
                                            <button
                                                key={tag.id}
                                                onClick={() =>
                                                    toggleNewTag(tag.id)
                                                }
                                                className={`inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full transition ${
                                                    newTagIds.includes(tag.id)
                                                        ? "text-white shadow-sm"
                                                        : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                                                }`}
                                                style={
                                                    newTagIds.includes(tag.id)
                                                        ? {
                                                              backgroundColor:
                                                                  tag.color,
                                                          }
                                                        : {}
                                                }
                                            >
                                                {tag.icon && (
                                                    <span className="text-xs">
                                                        {tag.icon}
                                                    </span>
                                                )}
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

            {/* === EDIT TASK PANEL === */}
            {editOpen && editTask && (
                <>
                    <div
                        className="fixed inset-0 bg-black/30 z-40"
                        onClick={() => setEditOpen(false)}
                        style={{ animation: "fadeIn 0.2s ease-out" }}
                    ></div>
                    <div
                        className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
                        style={{ animation: "slideIn 0.3s ease-out" }}
                    >
                        {/* Header */}
                        <div className="flex items-center gap-3 px-6 py-3 border-b border-gray-100 flex-shrink-0">
                            <button
                                onClick={() => toggleStatus(editTask)}
                                className={`w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center transition ${
                                    editTask.status === "done"
                                        ? "bg-brand-500 border-brand-500"
                                        : editTask.status === "wont_do"
                                          ? "bg-red-500 border-red-500"
                                          : "border-gray-300 hover:border-brand-400"
                                }`}
                            >
                                {editTask.status === "done" && (
                                    <svg
                                        className="w-3 h-3 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="3"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                )}
                                {editTask.status === "wont_do" && (
                                    <svg
                                        className="w-3 h-3 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="3"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                )}
                            </button>
                            <div className="w-px h-4 bg-gray-200"></div>
                            <DatePicker
                                value={editTask.due_date || ""}
                                onChange={(v) => saveField("due_date", v)}
                                iconMode
                                label="Due Date"
                            />
                            <div className="flex-1"></div>
                            <PriorityPicker
                                value={editTask.priority}
                                onChange={(v) => saveField("priority", v)}
                            />
                            <button
                                onClick={() => setEditOpen(false)}
                                className="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition hover:bg-gray-50"
                            >
                                <svg
                                    className="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        {/* Body */}
                        <div className="flex-1 overflow-y-auto min-h-0 flex flex-col">
                            {/* Title */}
                            <div className="px-6 pt-6 pb-3 flex-shrink-0">
                                <input
                                    type="text"
                                    value={editTask.title}
                                    onChange={(e) =>
                                        setEditTask({
                                            ...editTask,
                                            title: e.target.value,
                                        })
                                    }
                                    onBlur={() =>
                                        saveField("title", editTask.title)
                                    }
                                    className="w-full text-lg font-semibold text-gray-900 outline-none ring-0 border-0 bg-transparent placeholder-gray-300 focus:outline-none focus:ring-0 focus:border-0 focus:shadow-none"
                                    placeholder="Task title..."
                                />
                            </div>

                            <div className="border-t border-gray-100 mx-6 flex-shrink-0" />

                            {/* Description - fills remaining space */}
                            <div className="px-6 py-4 flex-1 min-h-0">
                                <TiptapEditor
                                    content={descHtml}
                                    onUpdate={(html) => saveDescription(html)}
                                    placeholder="Type something..."
                                />
                            </div>

                            {/* Subtasks - only if exist */}
                            {(editTask.subtasks?.length > 0 ||
                                editingNewSubtask) && (
                                <>
                                    <div className="border-t border-gray-100 mx-6 flex-shrink-0" />
                                    <div className="px-6 py-4 flex-shrink-0">
                                        <div className="flex items-center justify-between mb-2">
                                            <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                                Subtasks{" "}
                                                {editTask.subtasks?.length >
                                                    0 &&
                                                    `(${editTask.subtasks.filter((s) => s.is_completed).length}/${editTask.subtasks.length})`}
                                            </label>
                                        </div>
                                        <div className="space-y-1">
                                            {editTask.subtasks.map(
                                                (subtask) => (
                                                    <div
                                                        key={subtask.id}
                                                        className="flex items-center gap-2 group py-1"
                                                    >
                                                        <button
                                                            onClick={() =>
                                                                toggleSubtask(
                                                                    subtask,
                                                                )
                                                            }
                                                            className={`w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition ${
                                                                subtask.is_completed
                                                                    ? "bg-brand-500 border-brand-500"
                                                                    : "border-gray-300 hover:border-brand-400"
                                                            }`}
                                                        >
                                                            {subtask.is_completed && (
                                                                <svg
                                                                    className="w-2.5 h-2.5 text-white"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    viewBox="0 0 24 24"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth="3"
                                                                        d="M5 13l4 4L19 7"
                                                                    />
                                                                </svg>
                                                            )}
                                                        </button>
                                                        <span
                                                            className={`flex-1 text-xs ${subtask.is_completed ? "line-through text-gray-400" : "text-gray-700"}`}
                                                        >
                                                            {subtask.title}
                                                        </span>
                                                        <button
                                                            onClick={() =>
                                                                deleteSubtask(
                                                                    subtask,
                                                                )
                                                            }
                                                            className="p-0.5 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
                                                        >
                                                            <svg
                                                                className="w-3 h-3"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth="2"
                                                                    d="M6 18L18 6M6 6l12 12"
                                                                />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                        {editingNewSubtask ? (
                                            <div className="flex items-center gap-2 mt-1 py-1">
                                                <div className="w-4 h-4 rounded border border-gray-300 flex-shrink-0" />
                                                <input
                                                    type="text"
                                                    value={newSubtaskTitle}
                                                    autoFocus
                                                    onChange={(e) =>
                                                        setNewSubtaskTitle(
                                                            e.target.value,
                                                        )
                                                    }
                                                    onKeyDown={(e) => {
                                                        if (
                                                            e.key === "Enter" &&
                                                            newSubtaskTitle.trim()
                                                        )
                                                            addSubtask();
                                                        if (
                                                            e.key === "Escape"
                                                        ) {
                                                            setEditingNewSubtask(
                                                                false,
                                                            );
                                                            setNewSubtaskTitle(
                                                                "",
                                                            );
                                                        }
                                                    }}
                                                    onBlur={() => {
                                                        if (
                                                            newSubtaskTitle.trim()
                                                        )
                                                            addSubtask();
                                                        else {
                                                            setEditingNewSubtask(
                                                                false,
                                                            );
                                                            setNewSubtaskTitle(
                                                                "",
                                                            );
                                                        }
                                                    }}
                                                    className="flex-1 text-xs outline-none text-gray-700 placeholder-gray-300"
                                                    placeholder="Subtask name..."
                                                />
                                            </div>
                                        ) : (
                                            <button
                                                onClick={() =>
                                                    setEditingNewSubtask(true)
                                                }
                                                className="flex items-center gap-1.5 mt-1 py-1 text-xs text-gray-400 hover:text-brand-500 transition"
                                            >
                                                <svg
                                                    className="w-3.5 h-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                    />
                                                </svg>
                                                Add subtask
                                            </button>
                                        )}
                                    </div>
                                </>
                            )}

                            {/* Comments - only if exist or open */}
                            {commentsOpen && (
                                <>
                                    <div className="border-t border-gray-100 mx-6 flex-shrink-0" />
                                    <div className="px-6 py-4 flex-shrink-0">
                                        <div className="space-y-3 mb-3">
                                            {editTask.comments?.map(
                                                (comment) => (
                                                    <div
                                                        key={comment.id}
                                                        className="flex gap-2"
                                                    >
                                                        <div className="w-6 h-6 rounded-full bg-brand-50 text-brand-500 flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5">
                                                            {comment.user_name
                                                                ?.charAt(0)
                                                                .toUpperCase()}
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <p className="text-xs text-gray-800">
                                                                {comment.body}
                                                            </p>
                                                            <div className="flex items-center gap-2 mt-0.5">
                                                                <span className="text-[10px] text-gray-400">
                                                                    {
                                                                        comment.user_name
                                                                    }
                                                                </span>
                                                                <span className="text-[10px] text-gray-300">
                                                                    ·
                                                                </span>
                                                                <span className="text-[10px] text-gray-400">
                                                                    {
                                                                        comment.created_at
                                                                    }
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                        <form
                                            onSubmit={submitComment}
                                            className="flex gap-2"
                                        >
                                            <textarea
                                                value={commentBody}
                                                onChange={(e) =>
                                                    setCommentBody(
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                                maxLength="1000"
                                                placeholder="Write a comment..."
                                                className="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                                                rows="2"
                                            />
                                            <button
                                                type="submit"
                                                className="bg-brand-500 hover:bg-brand-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition self-end"
                                            >
                                                Send
                                            </button>
                                        </form>
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Bottom Bar */}
                        <div className="flex items-center justify-between px-4 py-2.5 border-t border-gray-100 flex-shrink-0">
                            <div className="flex items-center gap-1">
                                <button
                                    onClick={() =>
                                        setCommentsOpen(!commentsOpen)
                                    }
                                    className={`p-2 rounded-lg transition ${commentsOpen ? "bg-brand-50 text-brand-500" : "text-gray-400 hover:text-gray-600 hover:bg-gray-50"}`}
                                    title="Comments"
                                >
                                    <svg
                                        className="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                        />
                                    </svg>
                                </button>
                                <a
                                    href={`/pomodoro?task_id=${editTask.id}`}
                                    className="p-2 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50 transition"
                                    title="Start Focus Session"
                                >
                                    <svg
                                        className="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </a>
                            </div>

                            <div className="relative">
                                <button
                                    onClick={() =>
                                        setEditMenuOpen(!editMenuOpen)
                                    }
                                    className="p-2 rounded-lg transition text-gray-400 hover:text-gray-600 hover:bg-gray-50"
                                >
                                    <svg
                                        className="w-5 h-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle cx="5" cy="12" r="1.5" />
                                        <circle cx="12" cy="12" r="1.5" />
                                        <circle cx="19" cy="12" r="1.5" />
                                    </svg>
                                </button>

                                {editMenuOpen && (
                                    <>
                                        <div
                                            className="fixed inset-0 z-40"
                                            onClick={() =>
                                                setEditMenuOpen(false)
                                            }
                                        ></div>
                                        <div className="absolute bottom-full right-0 mb-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5">
                                            <button
                                                onClick={() => {
                                                    setEditingNewSubtask(true);
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <svg
                                                    className="w-4 h-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                    />
                                                </svg>
                                                Add Subtask
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <svg
                                                    className="w-4 h-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                                    />
                                                </svg>
                                                Link Parent Task
                                            </button>
                                            <button
                                                onClick={() => {
                                                    saveField(
                                                        "is_pinned",
                                                        !editTask.is_pinned,
                                                    );
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <svg
                                                    className="w-4 h-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                                    />
                                                </svg>
                                                {editTask.is_pinned
                                                    ? "Unpin"
                                                    : "Pin"}
                                            </button>
                                            {/* Tags submenu */}
                                            {tags.length > 0 && (
                                                <div className="px-4 py-2 border-t border-gray-100">
                                                    <span className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                                        Tags
                                                    </span>
                                                    <div className="flex flex-wrap gap-1 mt-1.5">
                                                        {tags.map((tag) => (
                                                            <button
                                                                key={tag.id}
                                                                onClick={() =>
                                                                    toggleEditTag(
                                                                        tag.id,
                                                                    )
                                                                }
                                                                className={`inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full transition ${
                                                                    editTask.tag_ids?.includes(
                                                                        tag.id,
                                                                    )
                                                                        ? "text-white"
                                                                        : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                                                                }`}
                                                                style={
                                                                    editTask.tag_ids?.includes(
                                                                        tag.id,
                                                                    )
                                                                        ? {
                                                                              backgroundColor:
                                                                                  tag.color,
                                                                          }
                                                                        : {}
                                                                }
                                                            >
                                                                {tag.icon && (
                                                                    <span className="text-[9px]">
                                                                        {
                                                                            tag.icon
                                                                        }
                                                                    </span>
                                                                )}
                                                                {tag.name}
                                                            </button>
                                                        ))}
                                                    </div>
                                                </div>
                                            )}
                                            <button
                                                onClick={() => {
                                                    saveField(
                                                        "status",
                                                        "wont_do",
                                                    );
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50 transition"
                                            >
                                                <div className="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                                    <svg
                                                        className="w-3 h-3 text-red-500"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="3"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </div>
                                                Won't Do
                                            </button>
                                            <div className="border-t border-gray-100 my-1" />
                                            <button
                                                onClick={() => {
                                                    router.post(
                                                        "/tasks",
                                                        {
                                                            title:
                                                                editTask.title +
                                                                " (Copy)",
                                                            priority:
                                                                editTask.priority,
                                                            due_date:
                                                                editTask.due_date,
                                                            project_id:
                                                                editTask.project_id,
                                                            tag_ids:
                                                                editTask.tag_ids,
                                                            is_recurring: false,
                                                        },
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    );
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <svg
                                                    className="w-4 h-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                Duplicate
                                            </button>
                                            <button
                                                onClick={() => {
                                                    deleteTask(
                                                        tasks.findIndex(
                                                            (t) =>
                                                                t.id ===
                                                                editTask.id,
                                                        ),
                                                    );
                                                    setEditMenuOpen(false);
                                                }}
                                                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-500 hover:bg-red-50 transition"
                                            >
                                                <svg
                                                    className="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </>
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
