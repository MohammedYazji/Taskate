import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';

const csrfToken = () => document.querySelector('meta[name=csrf-token]').content;

function patchTask(id, body) {
    return fetch(`/tasks/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });
}

export default function ProjectTaskRow({ task, tags, projects, projectColor, isMenuOpen, onOpenMenu, onOpenEdit, onUpdate, onDuplicate, onDelete }) {
    const [sub, setSub] = useState(null);
    const [moveOpen, setMoveOpen] = useState(false);
    const [tagOpen, setTagOpen] = useState(false);
    const [subtaskTitle, setSubtaskTitle] = useState('');
    const [moveProject, setMoveProject] = useState(null);
    const menuRef = useRef(null);

    useEffect(() => {
        if (!isMenuOpen) {
            setSub(null);
            setMoveOpen(false);
            setTagOpen(false);
            setMoveProject(null);
            setSubtaskTitle('');
        }
    }, [isMenuOpen]);

    useEffect(() => {
        if (!isMenuOpen) return;
        const handler = (e) => {
            if (menuRef.current && !menuRef.current.contains(e.target)) onOpenMenu(null);
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [isMenuOpen, onOpenMenu]);

    const toggleStatus = () => {
        const newStatus = task.status === 'done' || task.status === 'wont_do' ? 'todo' : 'done';
        patchTask(task.id, { status: newStatus });
        onUpdate({ ...task, status: newStatus });
    };

    const setDueDate = (date) => {
        patchTask(task.id, { due_date: date });
        onUpdate({ ...task, due_date: date });
        onOpenMenu(null);
    };

    const setPriority = (priority) => {
        patchTask(task.id, { priority });
        onUpdate({ ...task, priority });
        onOpenMenu(null);
    };

    const addSubtask = () => {
        if (!subtaskTitle.trim()) return;
        fetch(`/tasks/${task.id}/subtasks`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ title: subtaskTitle }),
        })
            .then((r) => r.json())
            .then((st) => {
                onUpdate({ ...task, subtasks: [...(task.subtasks || []), { id: st.id, title: st.title, is_completed: false }] });
                setSubtaskTitle('');
                onOpenMenu(null);
            });
    };

    const toggleTag = (tagId) => {
        const tagIds = task.tag_ids.includes(tagId) ? task.tag_ids.filter((id) => id !== tagId) : [...task.tag_ids, tagId];
        patchTask(task.id, { tag_ids: tagIds });
        onUpdate({ ...task, tag_ids: tagIds });
    };

    const moveTo = (projectId, sectionId) => {
        patchTask(task.id, { project_id: projectId, section_id: sectionId });
        onOpenMenu(null);
        if (projectId !== task.project_id) onDelete(task, true);
        else onUpdate({ ...task, section_id: sectionId });
    };

    const wontDo = () => {
        patchTask(task.id, { status: 'wont_do' });
        onUpdate({ ...task, status: 'wont_do' });
        onOpenMenu(null);
    };

    const deleteTask = () => {
        if (!confirm('Delete this task?')) return;
        router.delete(`/tasks/${task.id}`, { preserveScroll: true, preserveState: true });
        onDelete(task);
        onOpenMenu(null);
    };

    const today = new Date().toISOString().slice(0, 10);
    const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

    return (
        <div
            className={`bg-white border ${task.status === 'todo' ? 'border-stone hover:shadow-tactile' : task.status === 'in_progress' ? 'border-ochre/30 shadow-tactile' : 'border-stone opacity-60'} p-4 rounded-[2rem] flex items-center gap-4 group transition-all cursor-pointer`}
            data-id={task.id}
            onClick={() => onOpenEdit(task)}
        >
            <button
                onClick={(e) => { e.stopPropagation(); toggleStatus(); }}
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
                <h4 className={`text-lg font-serif text-ink ${task.status === 'done' || task.status === 'wont_do' ? 'line-through text-textMuted' : ''}`}>
                    {task.title}
                </h4>
                <p className="text-xs text-textMuted mt-0.5">
                    {task.priority && `${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}`}
                    {task.priority && task.due_date && ' • '}
                    {task.due_date && new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                </p>
            </div>

            {task.assigned_to_name && (
                <div
                    className="w-8 h-8 rounded-full border-2 border-stone bg-paper flex items-center justify-center text-xs font-semibold text-textMuted flex-shrink-0"
                    title={`Assigned to ${task.assigned_to_name}`}
                >
                    {task.assigned_to_name.charAt(0).toUpperCase()}
                </div>
            )}

            <div ref={menuRef} className="relative flex-shrink-0" onClick={(e) => e.stopPropagation()}>
                <button
                    onClick={() => onOpenMenu(isMenuOpen ? null : task.id)}
                    className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition"
                >
                    <i className="ph ph-dots-three-vertical text-sm"></i>
                </button>

                {isMenuOpen && (
                    <div className="absolute top-full right-0 mt-2 bg-white border border-stone rounded-2xl shadow-floating z-[60] py-1 w-52">
                        <button onClick={() => setSub(sub === 'date' ? null : 'date')} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-calendar-blank text-textMuted text-sm"></i>
                            Date <i className="ph ph-caret-right ml-auto text-stone text-xs"></i>
                        </button>
                        {sub === 'date' && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-stone rounded-2xl shadow-floating py-1 w-44 z-[70]">
                                <button onClick={() => setDueDate(today)} className="w-full text-left px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">Today</button>
                                <button onClick={() => setDueDate(tomorrow)} className="w-full text-left px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">Tomorrow</button>
                                <hr className="my-1 border-stone" />
                                <div className="px-3 py-1.5">
                                    <input type="date" onChange={(e) => setDueDate(e.target.value)} className="w-full text-xs border border-stone rounded-xl px-2 py-1.5 outline-none focus:ring-1 focus:ring-ochre" />
                                </div>
                            </div>
                        )}

                        <button onClick={() => setSub(sub === 'priority' ? null : 'priority')} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-flag text-textMuted text-sm"></i>
                            Priority <i className="ph ph-caret-right ml-auto text-stone text-xs"></i>
                        </button>
                        {sub === 'priority' && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-stone rounded-2xl shadow-floating py-1 w-36 z-[70]">
                                <button onClick={() => setPriority('low')} className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"><span className="w-2 h-2 rounded-full bg-green-500" /> Low</button>
                                <button onClick={() => setPriority('medium')} className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"><span className="w-2 h-2 rounded-full bg-orange-400" /> Medium</button>
                                <button onClick={() => setPriority('high')} className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"><span className="w-2 h-2 rounded-full bg-red-500" /> High</button>
                            </div>
                        )}

                        <hr className="my-1 border-stone" />

                        <button onClick={() => setSub(sub === 'subtask' ? null : 'subtask')} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-plus-circle text-textMuted text-sm"></i>
                            Add subtask
                        </button>
                        {sub === 'subtask' && (
                            <div className="px-3 pb-2">
                                <div className="flex gap-1.5">
                                    <input
                                        type="text"
                                        value={subtaskTitle}
                                        onChange={(e) => setSubtaskTitle(e.target.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && addSubtask()}
                                        placeholder="Subtask..."
                                        className="flex-1 text-xs border border-stone rounded-xl px-2 py-1.5 outline-none focus:ring-1 focus:ring-ochre"
                                    />
                                    <button onClick={addSubtask} className="text-xs px-3 py-1.5 bg-ink text-paper rounded-xl hover:bg-ink/90 transition font-semibold">Add</button>
                                </div>
                            </div>
                        )}

                        <hr className="my-1 border-stone" />

                        <button onClick={() => setTagOpen(!tagOpen)} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-tag text-textMuted text-sm"></i>
                            Tags <i className="ph ph-caret-right ml-auto text-stone text-xs"></i>
                        </button>
                        {tagOpen && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-stone rounded-2xl shadow-floating py-1 w-44 max-h-48 overflow-y-auto z-[70]">
                                {tags.map((tag) => (
                                    <button key={tag.id} onClick={() => toggleTag(tag.id)} className={`w-full flex items-center gap-2 px-4 py-2.5 text-xs hover:bg-stone/20 transition ${task.tag_ids.includes(tag.id) ? 'font-semibold' : 'text-textMain'}`}>
                                        <div className="w-2 h-2 rounded-full flex-shrink-0" style={{ backgroundColor: tag.color }} />
                                        <span className="flex-1 text-left truncate">{tag.name}</span>
                                        {task.tag_ids.includes(tag.id) && (
                                            <i className="ph ph-check text-brand-600 text-xs"></i>
                                        )}
                                    </button>
                                ))}
                                {tags.length === 0 && <p className="px-4 py-2.5 text-xs text-textMuted">No tags</p>}
                            </div>
                        )}

                        <div>
                            <button onClick={() => { setMoveOpen(!moveOpen); setMoveProject(null); }} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                                <i className="ph ph-arrow-elbow-down-right text-textMuted text-sm"></i>
                                Move to <i className="ph ph-caret-right ml-auto text-stone text-xs"></i>
                            </button>
                            {moveOpen && (
                                <div className="absolute left-full top-0 ml-1 bg-white border border-stone rounded-2xl shadow-floating py-1 w-56 max-h-72 overflow-y-auto z-[70]">
                                    {!moveProject && projects.map((p) => (
                                        <button
                                            key={p.id}
                                            onClick={() => {
                                                if (p.sections && p.sections.length > 0) setMoveProject(p);
                                                else moveTo(p.id, null);
                                            }}
                                            className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition"
                                        >
                                            <span>{p.icon || '📁'}</span>
                                            <span className="flex-1 text-left truncate">{p.name}</span>
                                            {p.sections?.length > 0 && (
                                                <i className="ph ph-caret-right text-stone text-xs"></i>
                                            )}
                                        </button>
                                    ))}
                                    {moveProject && (
                                        <>
                                            <button onClick={() => setMoveProject(null)} className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMuted hover:bg-stone/20 transition">
                                                <i className="ph ph-arrow-left text-xs"></i>
                                                <span>Back</span>
                                            </button>
                                            <hr className="my-1 border-stone" />
                                            <div className="px-4 py-1.5">
                                                <span className="text-[10px] font-semibold text-textMuted uppercase tracking-wide">{moveProject.icon || '📁'} {moveProject.name}</span>
                                            </div>
                                            <button onClick={() => moveTo(moveProject.id, null)} className="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                                                <span className="text-stone">—</span>
                                                <span>No section</span>
                                            </button>
                                            {moveProject.sections.map((s) => (
                                                <button key={s.id} onClick={() => moveTo(moveProject.id, s.id)} className="w-full flex items-center gap-2 px-4 py-2.5 pl-8 text-xs text-textMain hover:bg-stone/20 transition">
                                                    <span className="text-stone">—</span>
                                                    <span>{s.name}</span>
                                                </button>
                                            ))}
                                        </>
                                    )}
                                </div>
                            )}
                        </div>

                        <hr className="my-1 border-stone" />

                        <button onClick={() => { onDuplicate(task); onOpenMenu(null); }} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-copy text-textMuted text-sm"></i>
                            Duplicate
                        </button>

                        <hr className="my-1 border-stone" />

                        <button onClick={wontDo} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-textMain hover:bg-stone/20 transition">
                            <i className="ph ph-x-circle text-textMuted text-sm"></i>
                            Won't Do
                        </button>

                        <hr className="my-1 border-stone" />

                        <button onClick={deleteTask} className="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-terracotta hover:bg-terracotta/5 transition">
                            <i className="ph ph-trash text-sm"></i>
                            Delete
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
