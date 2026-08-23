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
            className="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition group cursor-pointer border-t border-gray-50 task-row"
            data-id={task.id}
            onClick={() => onOpenEdit(task)}
        >
            <button
                onClick={(e) => { e.stopPropagation(); toggleStatus(); }}
                className="w-[18px] h-[18px] rounded-[5px] border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition"
                style={{
                    backgroundColor: task.status === 'done' ? projectColor : task.status === 'wont_do' ? '#9CA3AF' : 'transparent',
                    borderColor: task.status === 'done' || task.status === 'wont_do' ? 'transparent' : '#D1D5DB',
                }}
            >
                {task.status === 'done' && (
                    <svg className="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                    </svg>
                )}
                {task.status === 'wont_do' && (
                    <svg className="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M18 6L6 18M6 6l12 12" />
                    </svg>
                )}
            </button>

            <span className={`flex-1 text-sm ${task.status === 'done' || task.status === 'wont_do' ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                {task.title}
            </span>

            <span className={`text-[11px] font-medium px-1.5 py-0.5 rounded border ${
                task.priority === 'high' ? 'text-red-600 bg-red-50 border-red-100'
                    : task.priority === 'medium' ? 'text-orange-500 bg-orange-50 border-orange-100'
                    : task.priority === 'low' ? 'text-green-600 bg-green-50 border-green-100'
                    : 'text-gray-500 bg-gray-100'
            }`}>
                {task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : ''}
            </span>

            {task.due_date && (
                <span className="text-[11px] text-gray-400 flex-shrink-0">
                    {new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                </span>
            )}

            <div ref={menuRef} className="relative flex-shrink-0 opacity-0 group-hover:opacity-100 transition" onClick={(e) => e.stopPropagation()}>
                <button
                    onClick={() => onOpenMenu(isMenuOpen ? null : task.id)}
                    className="p-1 text-gray-400 hover:text-gray-600 rounded transition"
                >
                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="12" cy="19" r="1.5" /></svg>
                </button>

                {isMenuOpen && (
                    <div className="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-[60] py-1 w-52">
                        <button onClick={() => setSub(sub === 'date' ? null : 'date')} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                            Date <svg className="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                        </button>
                        {sub === 'date' && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-44 z-[70]">
                                <button onClick={() => setDueDate(today)} className="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Today</button>
                                <button onClick={() => setDueDate(tomorrow)} className="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">Tomorrow</button>
                                <hr className="my-1 border-gray-100" />
                                <div className="px-3 py-1.5">
                                    <input type="date" onChange={(e) => setDueDate(e.target.value)} className="w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1 outline-none focus:ring-1 focus:ring-brand-500" />
                                </div>
                            </div>
                        )}

                        <button onClick={() => setSub(sub === 'priority' ? null : 'priority')} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" /><line x1="4" y1="22" x2="4" y2="15" /></svg>
                            Priority <svg className="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                        </button>
                        {sub === 'priority' && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-36 z-[70]">
                                <button onClick={() => setPriority('low')} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"><span className="w-2 h-2 rounded-full bg-green-500" /> Low</button>
                                <button onClick={() => setPriority('medium')} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"><span className="w-2 h-2 rounded-full bg-orange-400" /> Medium</button>
                                <button onClick={() => setPriority('high')} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"><span className="w-2 h-2 rounded-full bg-red-500" /> High</button>
                            </div>
                        )}

                        <hr className="my-1 border-gray-100" />

                        <button onClick={() => setSub(sub === 'subtask' ? null : 'subtask')} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
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
                                        className="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-1 focus:ring-brand-500"
                                    />
                                    <button onClick={addSubtask} className="text-xs px-2 py-1.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition">Add</button>
                                </div>
                            </div>
                        )}

                        <hr className="my-1 border-gray-100" />

                        <button onClick={() => setTagOpen(!tagOpen)} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg>
                            Tags <svg className="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                        </button>
                        {tagOpen && (
                            <div className="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-44 max-h-48 overflow-y-auto z-[70]">
                                {tags.map((tag) => (
                                    <button key={tag.id} onClick={() => toggleTag(tag.id)} className={`w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition ${task.tag_ids.includes(tag.id) ? 'font-semibold' : 'text-gray-600'}`}>
                                        <div className="w-2 h-2 rounded-full flex-shrink-0" style={{ backgroundColor: tag.color }} />
                                        <span className="flex-1 text-left truncate">{tag.name}</span>
                                        {task.tag_ids.includes(tag.id) && (
                                            <svg className="w-3 h-3 text-brand-500" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
                                        )}
                                    </button>
                                ))}
                                {tags.length === 0 && <p className="px-3 py-2 text-xs text-gray-400">No tags</p>}
                            </div>
                        )}

                        <div>
                            <button onClick={() => { setMoveOpen(!moveOpen); setMoveProject(null); }} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                                Move to <svg className="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                            </button>
                            {moveOpen && (
                                <div className="absolute left-full top-0 ml-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 w-56 max-h-72 overflow-y-auto z-[70]">
                                    {!moveProject && projects.map((p) => (
                                        <button
                                            key={p.id}
                                            onClick={() => {
                                                if (p.sections && p.sections.length > 0) setMoveProject(p);
                                                else moveTo(p.id, null);
                                            }}
                                            className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                        >
                                            <span>{p.icon || '📁'}</span>
                                            <span className="flex-1 text-left truncate">{p.name}</span>
                                            {p.sections?.length > 0 && (
                                                <svg className="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" /></svg>
                                            )}
                                        </button>
                                    ))}
                                    {moveProject && (
                                        <>
                                            <button onClick={() => setMoveProject(null)} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 transition">
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" /></svg>
                                                <span>Back</span>
                                            </button>
                                            <hr className="my-1 border-gray-100" />
                                            <div className="px-3 py-1.5">
                                                <span className="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{moveProject.icon || '📁'} {moveProject.name}</span>
                                            </div>
                                            <button onClick={() => moveTo(moveProject.id, null)} className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                                                <span className="text-gray-300">-</span>
                                                <span>No section</span>
                                            </button>
                                            {moveProject.sections.map((s) => (
                                                <button key={s.id} onClick={() => moveTo(moveProject.id, s.id)} className="w-full flex items-center gap-2 px-3 py-2 pl-6 text-xs text-gray-600 hover:bg-gray-50 transition">
                                                    <span className="text-gray-300">-</span>
                                                    <span>{s.name}</span>
                                                </button>
                                            ))}
                                        </>
                                    )}
                                </div>
                            )}
                        </div>

                        <hr className="my-1 border-gray-100" />

                        <button onClick={() => { onDuplicate(task); onOpenMenu(null); }} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" /></svg>
                            Duplicate
                        </button>

                        <hr className="my-1 border-gray-100" />

                        <button onClick={wontDo} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                            <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" /></svg>
                            Won't Do
                        </button>

                        <hr className="my-1 border-gray-100" />

                        <button onClick={deleteTask} className="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Delete
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
