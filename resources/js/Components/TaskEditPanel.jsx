import { useState, useRef, useCallback, useEffect } from "react";
import { router } from "@inertiajs/react";
import DatePicker from "@/Components/DatePicker";
import TiptapEditor from "@/Components/TiptapEditor";
import PriorityPicker from "@/Components/PriorityPicker";
import AssigneePicker from "@/Components/AssigneePicker";

export default function TaskEditPanel({ task, tags, members = [], onClose, onTaskUpdate }) {
    const [editTask, setEditTask] = useState(JSON.parse(JSON.stringify(task)));
    const [editMenuOpen, setEditMenuOpen] = useState(false);
    const [commentsOpen, setCommentsOpen] = useState(false);
    const [newSubtaskTitle, setNewSubtaskTitle] = useState("");
    const [editingNewSubtask, setEditingNewSubtask] = useState(false);
    const [commentBody, setCommentBody] = useState("");
    const saveTimerRef = useRef(null);
    const titleTimerRef = useRef(null);

    const editTaskRef = useRef(editTask);

    useEffect(() => {
        editTaskRef.current = editTask;
    }, [editTask]);

    useEffect(() => {
        setEditTask(JSON.parse(JSON.stringify(task)));
    }, [task]);

    const handleTitleChange = (e) => {
        const value = e.target.value;
        setEditTask((prev) => ({ ...prev, title: value }));
        clearTimeout(titleTimerRef.current);
        titleTimerRef.current = setTimeout(() => {
            const current = editTaskRef.current;
            if (!current?.id) return;
            router.patch(
                `/tasks/${current.id}`,
                { title: value },
                { preserveScroll: true, preserveState: true },
            );
        }, 200);
    };

    const handleTitleBlur = () => {
        clearTimeout(titleTimerRef.current);
        const current = editTaskRef.current;
        if (!current) return;
        if (!current.id) {
            if (!current.title.trim() || current.title.length < 3) return;
            const payload = {
                title: current.title,
                priority: current.priority || "medium",
                due_date: current.due_date || null,
                is_recurring: !!current.recurrence_frequency,
                recurrence_frequency: current.recurrence_frequency || null,
                tag_ids: current.tag_ids || [],
                project_id: current.project_id || null,
            };
            fetch("/tasks", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content,
                },
                body: JSON.stringify(payload),
            })
                .then((r) => {
                    if (!r.ok) throw new Error(r.status);
                    return r.json();
                })
                .then((created) => {
                    if (created?.id) {
                        const full = {
                            ...current,
                            ...created,
                            status: created.status || current.status || "todo",
                        };
                        setEditTask(full);
                        onTaskUpdate(full);
                    }
                })
                .catch(() => {});
            return;
        }
        const updated = { ...current, title: current.title };
        onTaskUpdate(updated);
        router.patch(
            `/tasks/${current.id}`,
            { title: current.title },
            { preserveScroll: true, preserveState: true },
        );
    };

    const saveField = useCallback(
        (field, value) => {
            const updated = { ...editTask, [field]: value };
            setEditTask(updated);
            onTaskUpdate(updated);
            if (!updated.id) {
                clearTimeout(saveTimerRef.current);
                return;
            }
            clearTimeout(saveTimerRef.current);
            saveTimerRef.current = setTimeout(() => {
                router.patch(
                    `/tasks/${updated.id}`,
                    { [field]: value },
                    { preserveScroll: true, preserveState: true },
                );
            }, 500);
        },
        [editTask, onTaskUpdate],
    );

    const toggleStatus = () => {
        const newStatus =
            editTask.status === "done"
                ? "todo"
                : editTask.status === "wont_do"
                  ? "todo"
                  : "done";
        saveField("status", newStatus);
    };

    const toggleEditTag = (tagId) => {
        const newIds = editTask.tag_ids.includes(tagId)
            ? editTask.tag_ids.filter((id) => id !== tagId)
            : [...editTask.tag_ids, tagId];
        saveField("tag_ids", newIds);
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
        onTaskUpdate(updated);
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
        onTaskUpdate(updated);
    };

    const deleteTask = () => {
        if (!confirm("Are you sure you want to delete this task?")) return;
        router.delete(`/tasks/${editTask.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
        onClose();
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
                onSuccess: (page) => {
                    setEditTask({
                        ...editTask,
                        comments: [
                            ...(editTask.comments || []),
                            {
                                id: Date.now(),
                                body: commentBody,
                                user_name: page.props.auth?.user?.name || "You",
                                created_at: "just now",
                            },
                        ],
                    });
                    setCommentBody("");
                },
            },
        );
    };

    const completedSubtasks = editTask.subtasks?.filter((s) => s.is_completed).length || 0;
    const totalSubtasks = editTask.subtasks?.length || 0;
    const subtaskProgress = totalSubtasks > 0 ? Math.round((completedSubtasks / totalSubtasks) * 100) : 0;

    return (
        <>
            <div
                className="fixed inset-0 bg-ink/20 z-40"
                onClick={onClose}
                style={{ animation: "fadeIn 0.2s ease-out" }}
            />
            <div
                className="fixed top-0 right-0 h-full w-full md:w-[600px] bg-paper shadow-floating border-l border-stone z-50 flex flex-col"
                style={{ animation: "slideIn 0.3s ease-out" }}
            >
                {/* Header */}
                <header className="h-16 border-b border-stone px-8 flex items-center justify-between bg-paper/90 backdrop-blur-sm sticky top-0 z-10 flex-shrink-0">
                    <div className="flex items-center gap-6">
                        <button
                            onClick={toggleStatus}
                            className={`w-6 h-6 rounded border-2 flex-shrink-0 flex items-center justify-center transition-all hover:scale-105 ${
                                editTask.status === "done"
                                    ? "bg-brand-500 border-brand-500 text-paper"
                                    : editTask.status === "wont_do"
                                      ? "bg-stone border-stone text-textMuted"
                                      : "border-stone hover:border-brand-400"
                            }`}
                        >
                            {(editTask.status === "done" || editTask.status === "wont_do") && (
                                <i className={`ph ph-check text-xs font-bold ${editTask.status === "wont_do" ? "ph-x" : ""}`}></i>
                            )}
                        </button>
                        <div className="h-4 w-px bg-stone" />
                        <DatePicker
                            value={editTask.due_date || ""}
                            onChange={(v) => saveField("due_date", v)}
                            repeatValue={editTask.recurrence_frequency || ""}
                            onRepeat={(v) => {
                                saveField("recurrence_frequency", v);
                                if (v && !editTask.due_date) {
                                    const today = new Date();
                                    const ds = today.toISOString().split("T")[0];
                                    saveField("due_date", ds);
                                }
                            }}
                            iconMode
                            label="Set date"
                        />
                    </div>
                    <div className="flex items-center gap-6">
                        <PriorityPicker
                            value={editTask.priority}
                            onChange={(v) => saveField("priority", v)}
                        />
                        <button onClick={onClose} className="text-textMuted hover:text-ink transition-colors">
                            <i className="ph ph-x text-2xl"></i>
                        </button>
                    </div>
                </header>

                {/* Scrollable Content */}
                <div className="flex-1 overflow-y-auto px-10 py-12 space-y-12">
                    {/* Title */}
                    <section>
                        <input
                            type="text"
                            value={editTask.title}
                            onChange={handleTitleChange}
                            onBlur={handleTitleBlur}
                            className="w-full text-4xl font-serif text-ink italic outline-none ring-0 border-0 bg-transparent placeholder-textMuted"
                            placeholder="Task title..."
                        />
                        <div className="h-1 w-20 bg-ochre/20 mt-2 mb-10"></div>

                        {/* Description */}
                        <div className="min-h-[120px]">
                            <TiptapEditor
                                content={editTask.description || ""}
                                onUpdate={(html) => saveField("description", html)}
                                placeholder="Add description..."
                            />
                        </div>
                    </section>

                    {/* Assignee */}
                    {members.length > 0 && (
                        <section>
                            <h4 className="text-xs font-mono font-bold uppercase tracking-widest text-textMuted mb-4">Assignee</h4>
                            <AssigneePicker
                                members={members}
                                value={editTask.assigned_to_id}
                                onChange={(v) => saveField("assigned_to_id", v)}
                            />
                        </section>
                    )}

                    {/* Subtasks */}
                    {(editTask.subtasks?.length > 0 || editingNewSubtask) && (
                        <section>
                            <div className="flex items-center justify-between mb-6">
                                <h4 className="text-xs font-mono font-bold uppercase tracking-widest text-textMuted">
                                    Subtasks <span className="text-ink">({completedSubtasks}/{totalSubtasks})</span>
                                </h4>
                                {totalSubtasks > 0 && (
                                    <div className="w-32 h-1 bg-stone rounded-full overflow-hidden">
                                        <div className="h-full bg-brand-500 rounded-full transition-all" style={{ width: `${subtaskProgress}%` }}></div>
                                    </div>
                                )}
                            </div>

                            <div className="space-y-3">
                                {editTask.subtasks?.map((subtask) => (
                                    <div key={subtask.id} className="group flex items-center gap-4 p-3 hover:bg-stone/10 rounded-xl transition-colors cursor-pointer">
                                        <button
                                            onClick={() => toggleSubtask(subtask)}
                                            className={`w-5 h-5 rounded flex-shrink-0 flex items-center justify-center transition ${
                                                subtask.is_completed
                                                    ? "bg-brand-100 border border-brand-500 text-brand-600"
                                                    : "border border-stone hover:border-brand-400"
                                            }`}
                                        >
                                            {subtask.is_completed && (
                                                <i className="ph ph-check text-xs font-bold"></i>
                                            )}
                                        </button>
                                        <span className={`flex-1 text-sm ${subtask.is_completed ? "text-textMuted line-through" : "text-textMain"}`}>
                                            {subtask.title}
                                        </span>
                                        <button
                                            onClick={() => deleteSubtask(subtask)}
                                            className="opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-terracotta hover:border-terracotta/30 transition"
                                        >
                                            <i className="ph ph-trash text-xs"></i>
                                        </button>
                                    </div>
                                ))}

                                {editingNewSubtask ? (
                                    <div className="flex items-center gap-4 p-3">
                                        <div className="w-5 h-5 rounded border border-stone flex-shrink-0"></div>
                                        <input
                                            type="text"
                                            value={newSubtaskTitle}
                                            autoFocus
                                            onChange={(e) => setNewSubtaskTitle(e.target.value)}
                                            onKeyDown={(e) => {
                                                if (e.key === "Enter" && newSubtaskTitle.trim()) addSubtask();
                                                if (e.key === "Escape") {
                                                    setEditingNewSubtask(false);
                                                    setNewSubtaskTitle("");
                                                }
                                            }}
                                            onBlur={() => {
                                                if (newSubtaskTitle.trim()) addSubtask();
                                                else {
                                                    setEditingNewSubtask(false);
                                                    setNewSubtaskTitle("");
                                                }
                                            }}
                                            className="flex-1 text-sm outline-none text-textMain bg-transparent placeholder-textMuted"
                                            placeholder="Subtask name..."
                                        />
                                    </div>
                                ) : (
                                    <button
                                        onClick={() => setEditingNewSubtask(true)}
                                        className="flex items-center gap-3 p-3 text-brand-600 hover:underline transition-all"
                                    >
                                        <i className="ph ph-plus text-sm"></i>
                                        <span className="text-xs font-bold uppercase tracking-widest">Add subtask</span>
                                    </button>
                                )}
                            </div>
                        </section>
                    )}

                    {/* Comments */}
                    {commentsOpen && (
                        <section>
                            <h4 className="text-xs font-mono font-bold uppercase tracking-widest text-textMuted mb-6">Comments</h4>
                            <div className="space-y-4 mb-4">
                                {editTask.comments?.map((comment) => (
                                    <div key={comment.id} className="flex gap-3">
                                        <div className="w-8 h-8 rounded-full border border-stone bg-paper flex items-center justify-center text-xs font-semibold text-textMuted flex-shrink-0 mt-0.5">
                                            {comment.user_name?.charAt(0).toUpperCase()}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm text-textMain">{comment.body}</p>
                                            <div className="flex items-center gap-2 mt-1">
                                                <span className="text-[10px] text-textMuted">{comment.user_name}</span>
                                                <span className="text-[10px] text-stone">·</span>
                                                <span className="text-[10px] text-textMuted">{comment.created_at}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={submitComment} className="flex gap-3">
                                <textarea
                                    value={commentBody}
                                    onChange={(e) => setCommentBody(e.target.value)}
                                    required
                                    maxLength="1000"
                                    placeholder="Write a comment..."
                                    className="flex-1 border border-stone rounded-2xl px-4 py-2.5 text-sm outline-none focus:ring-1 focus:ring-ochre focus:border-transparent resize-none bg-white placeholder-textMuted"
                                    rows="2"
                                />
                                <button
                                    type="submit"
                                    className="bg-ink hover:bg-ink/90 text-paper text-xs font-bold px-4 py-2 rounded-2xl transition self-end uppercase tracking-widest"
                                >
                                    Send
                                </button>
                            </form>
                        </section>
                    )}
                </div>

                {/* Footer */}
                <footer className="h-20 border-t border-stone px-8 flex items-center justify-between bg-paper/90 backdrop-blur-sm flex-shrink-0">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setCommentsOpen(!commentsOpen)}
                            className={`w-10 h-10 rounded-full border transition-all ${
                                commentsOpen ? "border-brand-500 bg-brand-50 text-brand-600" : "border-stone text-textMuted hover:text-ink"
                            } flex items-center justify-center`}
                            title="Comments"
                        >
                            <i className="ph ph-chat-circle-dots text-xl"></i>
                        </button>
                        <a
                            href={`/pomodoro?task_id=${editTask.id}`}
                            className="w-10 h-10 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition-all"
                            title="Start Focus Session"
                        >
                            <i className="ph ph-clock-counter-clockwise text-xl"></i>
                        </a>
                    </div>

                    <div className="relative">
                        <button
                            onClick={() => setEditMenuOpen(!editMenuOpen)}
                            className="w-10 h-10 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition-all"
                        >
                            <i className="ph ph-dots-three-outline text-2xl"></i>
                        </button>

                        {editMenuOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setEditMenuOpen(false)} />
                                <div className="absolute bottom-full right-0 mb-2 w-56 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1">
                                    <button
                                        onClick={() => {
                                            setEditingNewSubtask(true);
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"
                                    >
                                        <i className="ph ph-plus-circle text-textMuted text-sm"></i>
                                        Add Subtask
                                    </button>
                                    <button
                                        onClick={() => setEditMenuOpen(false)}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"
                                    >
                                        <i className="ph ph-link text-textMuted text-sm"></i>
                                        Link Parent Task
                                    </button>
                                    <button
                                        onClick={() => {
                                            saveField("is_pinned", !editTask.is_pinned);
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"
                                    >
                                        <i className="ph ph-pushpin text-textMuted text-sm"></i>
                                        {editTask.is_pinned ? "Unpin" : "Pin"}
                                    </button>
                                    {tags.length > 0 && (
                                        <div className="px-4 py-2 border-t border-stone">
                                            <span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Tags</span>
                                            <div className="flex flex-wrap gap-1 mt-1.5">
                                                {tags.map((tag) => (
                                                    <button
                                                        key={tag.id}
                                                        onClick={() => toggleEditTag(tag.id)}
                                                        className={`inline-flex items-center gap-1 text-[10px] font-medium px-2.5 py-1 rounded-full transition ${
                                                            editTask.tag_ids?.includes(tag.id)
                                                                ? "text-white shadow-sm"
                                                                : "bg-white border border-stone text-textMuted hover:bg-stone/20"
                                                        }`}
                                                        style={editTask.tag_ids?.includes(tag.id) ? { backgroundColor: tag.color } : {}}
                                                    >
                                                        {tag.icon && <span className="text-[9px]">{tag.icon}</span>}
                                                        {tag.name}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                    <button
                                        onClick={() => {
                                            saveField("status", "wont_do");
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-terracotta hover:bg-terracotta/5 transition"
                                    >
                                        <i className="ph ph-x-circle text-sm"></i>
                                        Won't Do
                                    </button>
                                    <div className="border-t border-stone my-1" />
                                    <button
                                        onClick={() => {
                                            router.post("/tasks", {
                                                title: editTask.title + " (Copy)",
                                                priority: editTask.priority,
                                                due_date: editTask.due_date,
                                                project_id: editTask.project_id,
                                                tag_ids: editTask.tag_ids,
                                                is_recurring: false,
                                            }, { preserveScroll: true });
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"
                                    >
                                        <i className="ph ph-copy text-textMuted text-sm"></i>
                                        Duplicate
                                    </button>
                                    <button
                                        onClick={() => {
                                            deleteTask();
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-terracotta hover:bg-terracotta/5 transition"
                                    >
                                        <i className="ph ph-trash text-sm"></i>
                                        Delete
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </footer>
            </div>

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
        </>
    );
}
