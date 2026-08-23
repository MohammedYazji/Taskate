import { useState, useRef, useCallback } from "react";
import { router } from "@inertiajs/react";
import DatePicker from "@/Components/DatePicker";
import TiptapEditor from "@/Components/TiptapEditor";
import PriorityPicker from "@/Components/PriorityPicker";

export default function TaskEditPanel({ task, tags, onClose, onTaskUpdate }) {
    const [editTask, setEditTask] = useState(JSON.parse(JSON.stringify(task)));
    const [editMenuOpen, setEditMenuOpen] = useState(false);
    const [commentsOpen, setCommentsOpen] = useState(false);
    const [descHtml, setDescHtml] = useState(task.description || "");
    const [newSubtaskTitle, setNewSubtaskTitle] = useState("");
    const [editingNewSubtask, setEditingNewSubtask] = useState(false);
    const [commentBody, setCommentBody] = useState("");
    const saveTimerRef = useRef(null);
    const descTimerRef = useRef(null);

    const saveField = useCallback(
        (field, value) => {
            const updated = { ...editTask, [field]: value };
            setEditTask(updated);
            onTaskUpdate(updated);
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

    const today = new Date();
    const todayStr = today.toISOString().split("T")[0];

    return (
        <>
            <div
                className="fixed inset-0 bg-black/30 z-40"
                onClick={onClose}
                style={{ animation: "fadeIn 0.2s ease-out" }}
            />
            <div
                className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
                style={{ animation: "slideIn 0.3s ease-out" }}
            >
                {/* Header */}
                <div className="flex items-center gap-3 px-6 py-3 border-b border-gray-100 flex-shrink-0">
                    <button
                        onClick={toggleStatus}
                        className={`w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center transition ${
                            editTask.status === "done"
                                ? "bg-brand-500 border-brand-500"
                                : editTask.status === "wont_do"
                                  ? "bg-red-500 border-red-500"
                                  : "border-gray-300 hover:border-brand-400"
                        }`}
                    >
                        {editTask.status === "done" && (
                            <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                            </svg>
                        )}
                        {editTask.status === "wont_do" && (
                            <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        )}
                    </button>
                    <div className="w-px h-4 bg-gray-200" />
                    <DatePicker
                        value={editTask.due_date || ""}
                        onChange={(v) => saveField("due_date", v)}
                        iconMode
                        label="Due Date"
                    />
                    <div className="flex-1" />
                    <PriorityPicker
                        value={editTask.priority}
                        onChange={(v) => saveField("priority", v)}
                    />
                    <button
                        onClick={onClose}
                        className="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition hover:bg-gray-50"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
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
                            onChange={(e) => setEditTask({ ...editTask, title: e.target.value })}
                            onBlur={() => saveField("title", editTask.title)}
                            className="w-full text-lg font-semibold text-gray-900 outline-none ring-0 border-0 bg-transparent placeholder-gray-300"
                            placeholder="Task title..."
                        />
                    </div>

                    <div className="border-t border-gray-100 mx-6 flex-shrink-0" />

                    {/* Description */}
                    <div className="px-6 py-4 flex-1 min-h-0">
                        <TiptapEditor
                            content={descHtml}
                            onUpdate={(html) => saveDescription(html)}
                            placeholder="Type something..."
                        />
                    </div>

                    {/* Subtasks */}
                    {(editTask.subtasks?.length > 0 || editingNewSubtask) && (
                        <>
                            <div className="border-t border-gray-100 mx-6 flex-shrink-0" />
                            <div className="px-6 py-4 flex-shrink-0">
                                <div className="flex items-center justify-between mb-2">
                                    <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                        Subtasks{" "}
                                        {editTask.subtasks?.length > 0 &&
                                            `(${editTask.subtasks.filter((s) => s.is_completed).length}/${editTask.subtasks.length})`}
                                    </label>
                                </div>
                                <div className="space-y-1">
                                    {editTask.subtasks?.map((subtask) => (
                                        <div key={subtask.id} className="flex items-center gap-2 group py-1">
                                            <button
                                                onClick={() => toggleSubtask(subtask)}
                                                className={`w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition ${
                                                    subtask.is_completed
                                                        ? "bg-brand-500 border-brand-500"
                                                        : "border-gray-300 hover:border-brand-400"
                                                }`}
                                            >
                                                {subtask.is_completed && (
                                                    <svg className="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                )}
                                            </button>
                                            <span className={`flex-1 text-xs ${subtask.is_completed ? "line-through text-gray-400" : "text-gray-700"}`}>
                                                {subtask.title}
                                            </span>
                                            <button
                                                onClick={() => deleteSubtask(subtask)}
                                                className="p-0.5 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
                                            >
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    ))}
                                </div>
                                {editingNewSubtask ? (
                                    <div className="flex items-center gap-2 mt-1 py-1">
                                        <div className="w-4 h-4 rounded border border-gray-300 flex-shrink-0" />
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
                                            className="flex-1 text-xs outline-none text-gray-700 placeholder-gray-300"
                                            placeholder="Subtask name..."
                                        />
                                    </div>
                                ) : (
                                    <button
                                        onClick={() => setEditingNewSubtask(true)}
                                        className="flex items-center gap-1.5 mt-1 py-1 text-xs text-gray-400 hover:text-brand-500 transition"
                                    >
                                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add subtask
                                    </button>
                                )}
                            </div>
                        </>
                    )}

                    {/* Comments */}
                    {commentsOpen && (
                        <>
                            <div className="border-t border-gray-100 mx-6 flex-shrink-0" />
                            <div className="px-6 py-4 flex-shrink-0">
                                <div className="space-y-3 mb-3">
                                    {editTask.comments?.map((comment) => (
                                        <div key={comment.id} className="flex gap-2">
                                            <div className="w-6 h-6 rounded-full bg-brand-50 text-brand-500 flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5">
                                                {comment.user_name?.charAt(0).toUpperCase()}
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-xs text-gray-800">{comment.body}</p>
                                                <div className="flex items-center gap-2 mt-0.5">
                                                    <span className="text-[10px] text-gray-400">{comment.user_name}</span>
                                                    <span className="text-[10px] text-gray-300">·</span>
                                                    <span className="text-[10px] text-gray-400">{comment.created_at}</span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <form onSubmit={submitComment} className="flex gap-2">
                                    <textarea
                                        value={commentBody}
                                        onChange={(e) => setCommentBody(e.target.value)}
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
                            onClick={() => setCommentsOpen(!commentsOpen)}
                            className={`p-2 rounded-lg transition ${commentsOpen ? "bg-brand-50 text-brand-500" : "text-gray-400 hover:text-gray-600 hover:bg-gray-50"}`}
                            title="Comments"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </button>
                        <a
                            href={`/pomodoro?task_id=${editTask.id}`}
                            className="p-2 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50 transition"
                            title="Start Focus Session"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>

                    <div className="relative">
                        <button
                            onClick={() => setEditMenuOpen(!editMenuOpen)}
                            className="p-2 rounded-lg transition text-gray-400 hover:text-gray-600 hover:bg-gray-50"
                        >
                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="5" cy="12" r="1.5" />
                                <circle cx="12" cy="12" r="1.5" />
                                <circle cx="19" cy="12" r="1.5" />
                            </svg>
                        </button>

                        {editMenuOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setEditMenuOpen(false)} />
                                <div className="absolute bottom-full right-0 mb-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5">
                                    <button
                                        onClick={() => {
                                            setEditingNewSubtask(true);
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Subtask
                                    </button>
                                    <button
                                        onClick={() => setEditMenuOpen(false)}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Link Parent Task
                                    </button>
                                    <button
                                        onClick={() => {
                                            saveField("is_pinned", !editTask.is_pinned);
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                        </svg>
                                        {editTask.is_pinned ? "Unpin" : "Pin"}
                                    </button>
                                    {tags.length > 0 && (
                                        <div className="px-4 py-2 border-t border-gray-100">
                                            <span className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Tags</span>
                                            <div className="flex flex-wrap gap-1 mt-1.5">
                                                {tags.map((tag) => (
                                                    <button
                                                        key={tag.id}
                                                        onClick={() => toggleEditTag(tag.id)}
                                                        className={`inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full transition ${
                                                            editTask.tag_ids?.includes(tag.id)
                                                                ? "text-white"
                                                                : "bg-gray-100 text-gray-600 hover:bg-gray-200"
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
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50 transition"
                                    >
                                        <div className="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <svg className="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        Won't Do
                                    </button>
                                    <div className="border-t border-gray-100 my-1" />
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
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Duplicate
                                    </button>
                                    <button
                                        onClick={() => {
                                            deleteTask();
                                            setEditMenuOpen(false);
                                        }}
                                        className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-500 hover:bg-red-50 transition"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
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
