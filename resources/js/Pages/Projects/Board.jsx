import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Sortable from 'sortablejs';
import ProjectMembersPanel from '@/Components/ProjectMembersPanel';
import WhoIsViewing from '@/Components/WhoIsViewing';
import echo from '@/echo';

const csrfToken = () => document.querySelector('meta[name=csrf-token]').content;

function jsonFetch(url, options) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'X-Socket-Id': echo.socketId() ?? '',
        },
        ...options,
    });
}

const PRIORITY_COLORS = {
    high: 'text-red-600 bg-red-50',
    medium: 'text-orange-500 bg-orange-50',
    low: 'text-green-600 bg-green-50',
};

export default function Board({ project, sections: initialSections, ungroupedTasks: initialUngrouped, members = [], currentUserRole = 'owner' }) {
    const [sections, setSections] = useState(initialSections);
    const [ungroupedTasks, setUngroupedTasks] = useState(initialUngrouped);
    const [sortOpen, setSortOpen] = useState(false);
    const [overflowOpen, setOverflowOpen] = useState(false);
    const [sectionMenuId, setSectionMenuId] = useState(null);
    const [renamingId, setRenamingId] = useState(null);
    const [renameName, setRenameName] = useState('');
    const [addingSection, setAddingSection] = useState(false);
    const [newSectionName, setNewSectionName] = useState('');
    const [addingTaskSectionId, setAddingTaskSectionId] = useState(null);
    const [addingTaskTitle, setAddingTaskTitle] = useState('');

    const columnRefs = useRef({});
    const sortableInstances = useRef([]);

    useEffect(() => { setSections(initialSections); }, [initialSections]);
    useEffect(() => { setUngroupedTasks(initialUngrouped); }, [initialUngrouped]);

    const refreshFromServer = () => {
        router.reload({ only: ['sections', 'ungroupedTasks'], preserveScroll: true });
    };

    useEffect(() => {
        const channel = echo.private(`project.${project.id}`);
        channel.listen('.task.moved', () => refreshFromServer());
        channel.listen('.task.updated', () => refreshFromServer());
        channel.listen('.task.deleted', () => refreshFromServer());

        return () => {
            echo.leave(`project.${project.id}`);
        };
    }, [project.id]);

    useEffect(() => {
        sortableInstances.current.forEach((s) => s.destroy());
        sortableInstances.current = [];

        Object.values(columnRefs.current).forEach((el) => {
            if (!el) return;
            const instance = new Sortable(el, {
                group: 'board',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: (evt) => {
                    const taskId = evt.item.dataset.id;
                    const sectionId = evt.to.dataset.sectionId || null;
                    const position = evt.newIndex;
                    jsonFetch(`/tasks/${taskId}/move`, {
                        method: 'PATCH',
                        body: JSON.stringify({ section_id: sectionId || null, position }),
                    }).then((res) => { if (!res.ok) refreshFromServer(); }).catch(() => refreshFromServer());
                },
            });
            sortableInstances.current.push(instance);
        });

        return () => {
            sortableInstances.current.forEach((s) => s.destroy());
            sortableInstances.current = [];
        };
    }, [sections, ungroupedTasks]);

    const addQuickTask = (sectionId) => {
        if (!addingTaskTitle.trim()) return;
        jsonFetch('/tasks', {
            method: 'POST',
            body: JSON.stringify({
                title: addingTaskTitle.trim(),
                project_id: project.id,
                section_id: sectionId,
                status: 'todo',
                priority: 'medium',
            }),
        }).then(() => refreshFromServer());
        setAddingTaskTitle('');
        setAddingTaskSectionId(null);
    };

    const addSection = () => {
        if (!newSectionName.trim()) return;
        jsonFetch(`/projects/${project.id}/sections`, {
            method: 'POST',
            body: JSON.stringify({ name: newSectionName.trim() }),
        }).then(() => refreshFromServer());
        setNewSectionName('');
        setAddingSection(false);
    };

    const renameSection = (id) => {
        if (!renameName.trim()) return;
        jsonFetch(`/sections/${id}`, {
            method: 'PATCH',
            body: JSON.stringify({ name: renameName.trim() }),
        }).then(() => refreshFromServer());
        setRenamingId(null);
    };

    const deleteSection = (id) => {
        if (!confirm('Delete this section? Tasks will be moved to ungrouped.')) return;
        jsonFetch(`/sections/${id}`, {
            method: 'DELETE',
            body: JSON.stringify({ target_section_id: null }),
        }).then(() => refreshFromServer());
    };

    const deleteProject = () => {
        if (!confirm('Delete this list?')) return;
        router.delete(route('projects.destroy', project.id));
    };

    const renderCard = (task) => (
        <div
            key={task.id}
            data-id={task.id}
            className="bg-white rounded-lg border border-gray-200 p-3 shadow-sm hover:shadow-md transition cursor-grab"
        >
            <p className="text-sm font-medium text-gray-900">{task.title}</p>
            {task.subtasks_total > 0 && (
                <p className="text-xs text-gray-400 mt-1">{task.subtasks_done}/{task.subtasks_total} subtasks</p>
            )}
            <div className="flex items-center justify-between mt-2">
                <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded uppercase tracking-wide ${PRIORITY_COLORS[task.priority] || 'text-gray-500 bg-gray-100'}`}>
                    {task.priority}
                </span>
                <div className="flex items-center gap-1.5">
                    {task.due_date && (
                        <span className="text-[10px] text-gray-400">
                            {new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                        </span>
                    )}
                    {task.assigned_to_name && (
                        <div
                            className="w-4 h-4 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-[9px] font-semibold flex-shrink-0"
                            title={`Assigned to ${task.assigned_to_name}`}
                        >
                            {task.assigned_to_name.charAt(0).toUpperCase()}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );

    return (
        <div className="max-w-4xl">
            <Head title={`${project.name} - Board`} />

            <div className="flex items-center justify-between mb-5">
                <div className="flex items-center gap-3">
                    <span className="text-xl flex-shrink-0">{project.icon}</span>
                    <h1 className="text-xl font-bold text-gray-900 truncate max-w-[400px]">{project.name}</h1>
                    {project.description && <p className="text-sm text-gray-500">{project.description}</p>}
                    <WhoIsViewing projectId={project.id} />
                </div>
                <div className="flex items-center gap-2">
                    <ProjectMembersPanel project={project} members={members} isOwner={currentUserRole === 'owner'} />

                    <div className="relative">
                        <button onClick={() => setSortOpen(!sortOpen)} className="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" /></svg>
                        </button>
                        {sortOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setSortOpen(false)} />
                                <div className="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                                    <div className="px-3 py-1.5"><span className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Sort by</span></div>
                                    <button className="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Priority</button>
                                    <button className="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Date</button>
                                    <button className="w-full text-left px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">Alphabetical</button>
                                </div>
                            </>
                        )}
                    </div>

                    <div className="relative">
                        <button onClick={() => setOverflowOpen(!overflowOpen)} className="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="12" cy="19" r="1.5" /></svg>
                        </button>
                        {overflowOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setOverflowOpen(false)} />
                                <div className="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                                    <div className="px-3 py-1.5"><span className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">View</span></div>
                                    <Link href={route('projects.show', project.id)} className="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                                        <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7" /></svg>
                                        List
                                    </Link>
                                    <Link href={route('projects.board', project.id)} className="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-brand-600 bg-brand-50 transition">
                                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" /></svg>
                                        Board
                                    </Link>
                                    <Link href={route('calendar', { project_id: project.id })} className="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">
                                        <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                                        Timeline
                                    </Link>
                                    <hr className="my-1 border-gray-100" />
                                    <button onClick={deleteProject} className="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 transition">
                                        Delete project
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            <div className="flex gap-4 overflow-x-auto pb-4 items-start">
                {sections.map((section) => (
                    <div key={section.id} className="w-72 flex-shrink-0 flex flex-col">
                        <div className="flex items-center justify-between mb-3">
                            {renamingId === section.id ? (
                                <input
                                    type="text"
                                    autoFocus
                                    value={renameName}
                                    onChange={(e) => setRenameName(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') renameSection(section.id);
                                        if (e.key === 'Escape') setRenamingId(null);
                                    }}
                                    className="text-sm font-semibold text-gray-700 border border-brand-300 rounded px-1.5 py-0.5 outline-none focus:ring-2 focus:ring-brand-500 w-full"
                                />
                            ) : (
                                <h3 className="text-sm font-semibold text-gray-700">{section.name}</h3>
                            )}
                            <div className="flex items-center gap-1.5">
                                <span className="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5">{section.tasks.length}</span>
                                <div className="relative">
                                    <button
                                        onClick={() => setSectionMenuId(sectionMenuId === section.id ? null : section.id)}
                                        className="p-0.5 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                                    >
                                        <svg className="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="12" cy="19" r="1.5" /></svg>
                                    </button>
                                    {sectionMenuId === section.id && (
                                        <>
                                            <div className="fixed inset-0 z-40" onClick={() => setSectionMenuId(null)} />
                                            <div className="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-44">
                                                <button
                                                    onClick={() => { setRenamingId(section.id); setRenameName(section.name); setSectionMenuId(null); }}
                                                    className="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                >
                                                    Rename
                                                </button>
                                                <button
                                                    onClick={() => { setSectionMenuId(null); setAddingTaskSectionId(section.id); }}
                                                    className="w-full text-left px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                >
                                                    Add Task
                                                </button>
                                                <hr className="my-1 border-gray-100" />
                                                <button
                                                    onClick={() => { setSectionMenuId(null); deleteSection(section.id); }}
                                                    className="w-full text-left px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div
                            ref={(el) => { columnRefs.current[section.id] = el; }}
                            data-section-id={section.id}
                            className="flex-1 space-y-2 min-h-[2rem]"
                        >
                            {section.tasks.map(renderCard)}
                            {section.tasks.length === 0 && (
                                <div className="text-center text-xs text-gray-400 py-6">No tasks</div>
                            )}
                        </div>

                        {addingTaskSectionId === section.id && (
                            <div className="mt-2">
                                <input
                                    type="text"
                                    autoFocus
                                    value={addingTaskTitle}
                                    onChange={(e) => setAddingTaskTitle(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') addQuickTask(section.id);
                                        if (e.key === 'Escape') { setAddingTaskSectionId(null); setAddingTaskTitle(''); }
                                    }}
                                    onBlur={() => { if (addingTaskTitle.trim()) addQuickTask(section.id); else setAddingTaskSectionId(null); }}
                                    placeholder="Task title..."
                                    className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                />
                            </div>
                        )}
                    </div>
                ))}

                {ungroupedTasks.length > 0 && (
                    <div className="w-72 flex-shrink-0 flex flex-col">
                        <div className="flex items-center justify-between mb-3">
                            <h3 className="text-sm font-semibold text-gray-700">No Section</h3>
                            <span className="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5">{ungroupedTasks.length}</span>
                        </div>
                        <div
                            ref={(el) => { columnRefs.current.none = el; }}
                            data-section-id=""
                            className="flex-1 space-y-2 min-h-[2rem]"
                        >
                            {ungroupedTasks.map(renderCard)}
                        </div>
                    </div>
                )}

                <div className="w-72 flex-shrink-0">
                    {!addingSection ? (
                        <button
                            onClick={() => setAddingSection(true)}
                            className="w-full flex items-center gap-2 px-3 py-2 rounded-lg border-2 border-dashed border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 transition text-sm"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                            Add Section
                        </button>
                    ) : (
                        <div className="bg-white rounded-lg border border-gray-200 p-3">
                            <input
                                type="text"
                                autoFocus
                                value={newSectionName}
                                onChange={(e) => setNewSectionName(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') addSection();
                                    if (e.key === 'Escape') { setAddingSection(false); setNewSectionName(''); }
                                }}
                                placeholder="Section name..."
                                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent mb-2"
                            />
                            <div className="flex gap-2">
                                <button onClick={addSection} className="flex-1 text-xs font-medium py-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition">Add</button>
                                <button onClick={() => { setAddingSection(false); setNewSectionName(''); }} className="flex-1 text-xs font-medium py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
