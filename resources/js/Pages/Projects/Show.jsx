import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Sortable from 'sortablejs';
import DatePicker from '@/Components/DatePicker';
import PriorityPicker from '@/Components/PriorityPicker';
import TaskEditPanel from '@/Components/TaskEditPanel';
import ProjectTaskRow from '@/Components/ProjectTaskRow';
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
        },
        ...options,
    });
}

const GROUP_LABELS = {
    status: { todo: 'To Do', in_progress: 'In Progress', done: 'Done', wont_do: "Won't Do" },
    priority: { high: 'High', medium: 'Medium', low: 'Low', none: 'No Priority' },
};
const GROUP_ORDER = {
    status: ['todo', 'in_progress', 'done', 'wont_do'],
    priority: ['high', 'medium', 'low', 'none'],
};

export default function Show({ project, sections: initialSections, tasks: initialTasks, tags, projects, members = [], currentUserRole = 'owner' }) {
    const [sections, setSections] = useState(initialSections);
    const [tasks, setTasks] = useState(initialTasks);
    const [collapsed, setCollapsed] = useState(() => {
        const state = {};
        initialSections.forEach((s) => {
            state[s.id] = localStorage.getItem(`project_${project.id}_section_${s.id}`) === 'false';
        });
        return state;
    });

    useEffect(() => {
        setSections(initialSections);
        setTasks(initialTasks);
        setCollapsed((prev) => {
            const state = { ...prev };
            initialSections.forEach((s) => {
                if (!(s.id in state)) {
                    state[s.id] = localStorage.getItem(`project_${project.id}_section_${s.id}`) === 'false';
                }
            });
            return state;
        });
    }, [initialSections]);

    useEffect(() => {
        setTasks(initialTasks);
    }, [initialTasks]);

    const [groupBy, setGroupBy] = useState('section');
    const [sortBy, setSortBy] = useState('manual');
    const [hideCompleted, setHideCompleted] = useState(false);
    const [sortMenuOpen, setSortMenuOpen] = useState(false);
    const [overflowMenuOpen, setOverflowMenuOpen] = useState(false);

    const [addingSection, setAddingSection] = useState(false);
    const [newSectionName, setNewSectionName] = useState('');
    const [editingSectionId, setEditingSectionId] = useState(null);
    const [editingSectionName, setEditingSectionName] = useState('');
    const [sectionMenuId, setSectionMenuId] = useState(null);

    const [deleteSectionId, setDeleteSectionId] = useState(null);
    const [deleteSectionTarget, setDeleteSectionTarget] = useState('');
    const [moveSectionId, setMoveSectionId] = useState(null);
    const [moveProjectId, setMoveProjectId] = useState('');
    const [moveSectionTarget, setMoveSectionTarget] = useState('');
    const [sectionModalOpen, setSectionModalOpen] = useState(false);
    const [sectionModalName, setSectionModalName] = useState('');
    const [sectionModalMode, setSectionModalMode] = useState('above');
    const [sectionModalParentId, setSectionModalParentId] = useState(null);

    const [taskMenuId, setTaskMenuId] = useState(null);
    const [editTask, setEditTask] = useState(null);

    const [newOpen, setNewOpen] = useState(false);
    const [newTitle, setNewTitle] = useState('');
    const [newPriority, setNewPriority] = useState('medium');
    const [newDate, setNewDate] = useState('');
    const [newSectionId, setNewSectionId] = useState(sections[0]?.id ?? null);
    const [newSectionDropdownOpen, setNewSectionDropdownOpen] = useState(false);

    const [quickTitle, setQuickTitle] = useState('');
    const [quickPriority, setQuickPriority] = useState('medium');
    const [quickDate, setQuickDate] = useState('');
    const [quickSectionId, setQuickSectionId] = useState(sections[0]?.id ?? '');
    const [quickTagIds, setQuickTagIds] = useState([]);
    const [quickMenuOpen, setQuickMenuOpen] = useState(false);

    const sectionRefs = useRef({});
    const sortableInstances = useRef([]);

    const refreshFromServer = () => {
        router.reload({ only: ['sections', 'tasks'], preserveScroll: true });
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
        if (!editTask || !initialTasks) return;
        const updated = initialTasks.find((t) => t.id === editTask.id);
        if (updated && JSON.stringify(updated) !== JSON.stringify(editTask)) {
            setEditTask(updated);
        }
    }, [initialTasks]);

    const toggleSection = (id) => {
        setCollapsed((prev) => {
            const next = { ...prev, [id]: !prev[id] };
            localStorage.setItem(`project_${project.id}_section_${id}`, String(!next[id]));
            return next;
        });
    };

    const updateTask = (updated) => {
        setTasks((prev) => prev.map((t) => (t.id === updated.id ? updated : t)));
        if (editTask?.id === updated.id) setEditTask(updated);
    };

    const removeTask = (task) => {
        setTasks((prev) => prev.filter((t) => t.id !== task.id));
        if (editTask?.id === task.id) setEditTask(null);
    };

    const duplicateTask = (task) => {
        jsonFetch('/tasks', {
            method: 'POST',
            body: JSON.stringify({
                title: task.title + ' (copy)',
                project_id: task.project_id,
                section_id: task.section_id,
                priority: task.priority || 'medium',
                status: 'todo',
                due_date: task.due_date || null,
            }),
        })
            .then((r) => r.json())
            .then((newTask) => {
                setTasks((prev) => [...prev, {
                    id: newTask.id,
                    title: newTask.title,
                    status: newTask.status,
                    priority: newTask.priority,
                    due_date: newTask.due_date,
                    description: '',
                    is_recurring: false,
                    section_id: newTask.section_id,
                    project_id: newTask.project_id,
                    tag_ids: [],
                    subtasks: [],
                    comments: [],
                }]);
            });
    };

    // === Sortable wiring ===
    useEffect(() => {
        sortableInstances.current.forEach((s) => s.destroy());
        sortableInstances.current = [];
        if (groupBy !== 'section') return;

        Object.values(sectionRefs.current).forEach((el) => {
            if (!el) return;
            const instance = new Sortable(el, {
                group: 'project-tasks',
                animation: 150,
                ghostClass: 'opacity-30',
                handle: '.task-row',
                onEnd: (evt) => {
                    const taskId = parseInt(evt.item.dataset.id, 10);
                    const newSection = evt.to.dataset.section || null;
                    const newPosition = evt.newIndex;
                    jsonFetch(`/tasks/${taskId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ section_id: newSection || null, position: newPosition }),
                    });
                    setTasks((prev) => prev.map((t) => (
                        t.id === taskId ? { ...t, section_id: newSection ? Number(newSection) : null, position: newPosition } : t
                    )));
                },
            });
            sortableInstances.current.push(instance);
        });

        return () => {
            sortableInstances.current.forEach((s) => s.destroy());
            sortableInstances.current = [];
        };
    }, [groupBy, sections]);

    // === Derived lists ===
    let filteredTasks = tasks;
    if (hideCompleted) filteredTasks = filteredTasks.filter((t) => t.status !== 'done' && t.status !== 'wont_do');
    if (sortBy === 'priority') {
        const order = { high: 0, medium: 1, low: 2 };
        filteredTasks = [...filteredTasks].sort((a, b) => (order[a.priority] ?? 3) - (order[b.priority] ?? 3));
    } else if (sortBy === 'date') {
        filteredTasks = [...filteredTasks].sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return a.due_date.localeCompare(b.due_date);
        });
    } else if (sortBy === 'alpha') {
        filteredTasks = [...filteredTasks].sort((a, b) => a.title.localeCompare(b.title));
    }

    let taskGroups = null;
    if (groupBy !== 'section') {
        if (groupBy === 'none') {
            taskGroups = [{ key: null, label: null, tasks: filteredTasks }];
        } else {
            const keyFn = groupBy === 'status' ? (t) => t.status : (t) => t.priority || 'none';
            const groupedMap = {};
            filteredTasks.forEach((t) => {
                const key = keyFn(t);
                if (!groupedMap[key]) groupedMap[key] = [];
                groupedMap[key].push(t);
            });
            taskGroups = GROUP_ORDER[groupBy]
                .filter((k) => groupedMap[k])
                .map((k) => ({ key: k, label: (GROUP_LABELS[groupBy] || {})[k] || k, tasks: groupedMap[k] }));
        }
    }

    const sectionCount = (id) => filteredTasks.filter((t) => String(t.section_id) === String(id)).length;

    // === Section actions ===
    const createSection = () => {
        if (!newSectionName.trim()) return;
        jsonFetch(`/projects/${project.id}/sections`, {
            method: 'POST',
            body: JSON.stringify({ name: newSectionName.trim() }),
        }).then(() => refreshFromServer());
        setNewSectionName('');
        setAddingSection(false);
    };

    const renameSection = (id) => {
        if (!editingSectionName.trim()) return;
        jsonFetch(`/sections/${id}`, {
            method: 'PATCH',
            body: JSON.stringify({ name: editingSectionName.trim() }),
        }).then(() => refreshFromServer());
        setEditingSectionId(null);
    };

    const confirmDeleteSection = () => {
        jsonFetch(`/sections/${deleteSectionId}`, {
            method: 'DELETE',
            body: JSON.stringify({ target_section_id: deleteSectionTarget || null }),
        }).then(() => {
            setDeleteSectionId(null);
            refreshFromServer();
        });
    };

    const submitSectionModal = () => {
        if (!sectionModalName.trim()) return;
        const url = sectionModalMode === 'above'
            ? `/sections/${sectionModalParentId}/above`
            : `/sections/${sectionModalParentId}/below`;
        jsonFetch(url, { method: 'POST', body: JSON.stringify({ name: sectionModalName.trim() }) })
            .then(() => {
                setSectionModalOpen(false);
                refreshFromServer();
            });
    };

    const confirmMoveSection = () => {
        jsonFetch(`/sections/${moveSectionId}/move`, {
            method: 'PATCH',
            body: JSON.stringify({ project_id: moveProjectId, section_id: moveSectionTarget || null }),
        }).then(() => {
            setMoveSectionId(null);
            router.visit(route('projects.show', moveProjectId));
        });
    };

    const deleteProject = () => {
        if (!confirm('Delete this list?')) return;
        router.delete(route('projects.destroy', project.id));
    };

    // === Quick add / new task ===
    const addQuickTask = () => {
        if (!quickTitle.trim()) return;
        jsonFetch('/tasks', {
            method: 'POST',
            body: JSON.stringify({
                title: quickTitle.trim(),
                project_id: project.id,
                section_id: quickSectionId || null,
                priority: quickPriority,
                status: 'todo',
                due_date: quickDate || null,
                tag_ids: quickTagIds,
            }),
        })
            .then((r) => r.json())
            .then((task) => {
                setTasks((prev) => [...prev, {
                    id: task.id,
                    title: task.title,
                    status: task.status,
                    priority: task.priority,
                    due_date: task.due_date,
                    description: '',
                    is_recurring: task.is_recurring,
                    section_id: task.section_id,
                    project_id: task.project_id,
                    tag_ids: quickTagIds,
                    subtasks: [],
                    comments: [],
                }]);
                setQuickTitle('');
                setQuickPriority('medium');
                setQuickDate('');
                setQuickSectionId(sections[0]?.id ?? '');
                setQuickTagIds([]);
            });
    };

    const toggleQuickTag = (tagId) => {
        setQuickTagIds((prev) => (prev.includes(tagId) ? prev.filter((id) => id !== tagId) : [...prev, tagId]));
    };

    const openNewTask = (sectionId) => {
        setNewSectionId(sectionId);
        setNewOpen(true);
    };

    const createTask = () => {
        if (!newTitle.trim()) return;
        router.post('/tasks', {
            title: newTitle,
            project_id: project.id,
            section_id: newSectionId,
            priority: newPriority,
            due_date: newDate || null,
            is_recurring: false,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setNewOpen(false);
                setNewTitle('');
                setNewPriority('medium');
                setNewDate('');
                refreshFromServer();
            },
        });
    };

    const sectionName = (id) => sections.find((s) => s.id === id)?.name || '';

    const renderTaskRow = (task) => (
        <ProjectTaskRow
            key={task.id}
            task={task}
            tags={tags}
            projects={projects}
            projectColor={project.color}
            isMenuOpen={taskMenuId === task.id}
            onOpenMenu={setTaskMenuId}
            onOpenEdit={(t) => setEditTask(JSON.parse(JSON.stringify(t)))}
            onUpdate={updateTask}
            onDuplicate={duplicateTask}
            onDelete={removeTask}
        />
    );

    return (
        <div className="max-w-4xl">
            <Head title={project.name} />

            {/* Project Header */}
            <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-3">
                    <span className="text-xl flex-shrink-0">{project.icon}</span>
                    <h1 className="text-xl font-serif font-bold text-ink truncate max-w-[400px]">{project.name}</h1>
                    {project.description && <p className="text-sm text-textMuted">{project.description}</p>}
                    <WhoIsViewing projectId={project.id} />
                </div>
                <div className="flex items-center gap-2">
                    <ProjectMembersPanel project={project} members={members} isOwner={currentUserRole === 'owner'} />

                    <button
                        onClick={() => { setAddingSection(true); }}
                        className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition"
                        title="Add section"
                    >
                        <i className="ph ph-plus text-sm"></i>
                    </button>

                    <div className="relative">
                        <button onClick={() => setSortMenuOpen(!sortMenuOpen)} className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition">
                            <i className="ph ph-funnel text-sm"></i>
                        </button>
                        {sortMenuOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setSortMenuOpen(false)} />
                                <div className="absolute top-full right-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1 w-44">
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Group by</span></div>
                                    {['none', 'status', 'priority', 'section'].map((g) => (
                                        <button key={g} onClick={() => setGroupBy(g)} className={`w-full flex items-center justify-between px-4 py-1.5 text-xs hover:bg-stone/20 transition capitalize ${groupBy === g ? 'text-brand-600 font-semibold' : 'text-textMain'}`}>
                                            {g}
                                            {groupBy === g && <i className="ph ph-check text-brand-600 text-xs"></i>}
                                        </button>
                                    ))}
                                    <hr className="my-1 border-stone" />
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Sort by</span></div>
                                    {[['manual', 'Manual'], ['priority', 'Priority'], ['date', 'Date'], ['alpha', 'Alphabetical']].map(([v, label]) => (
                                        <button key={v} onClick={() => setSortBy(v)} className={`w-full flex items-center justify-between px-4 py-1.5 text-xs hover:bg-stone/20 transition ${sortBy === v ? 'text-brand-600 font-semibold' : 'text-textMain'}`}>
                                            {label}
                                            {sortBy === v && <i className="ph ph-check text-brand-600 text-xs"></i>}
                                        </button>
                                    ))}
                                </div>
                            </>
                        )}
                    </div>

                    <div className="relative">
                        <button onClick={() => setOverflowMenuOpen(!overflowMenuOpen)} className="w-8 h-8 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition">
                            <i className="ph ph-dots-three-vertical text-sm"></i>
                        </button>
                        {overflowMenuOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setOverflowMenuOpen(false)} />
                                <div className="absolute top-full right-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1 w-44">
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">View</span></div>
                                    <Link href={route('projects.show', project.id)} className="w-full flex items-center gap-2 px-4 py-1.5 text-xs text-textMain hover:bg-stone/20 transition">
                                        <i className="ph ph-list text-textMuted text-sm"></i>
                                        List
                                    </Link>
                                    <Link href={route('projects.board', project.id)} className="w-full flex items-center gap-2 px-4 py-1.5 text-xs text-textMain hover:bg-stone/20 transition">
                                        <i className="ph ph-columns text-textMuted text-sm"></i>
                                        Board
                                    </Link>
                                    <Link href={route('calendar', { project_id: project.id })} className="w-full flex items-center gap-2 px-4 py-1.5 text-xs text-textMain hover:bg-stone/20 transition">
                                        <i className="ph ph-calendar text-textMuted text-sm"></i>
                                        Timeline
                                    </Link>
                                    <hr className="my-1 border-stone" />
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Display</span></div>
                                    <button onClick={() => setHideCompleted(!hideCompleted)} className={`w-full flex items-center gap-2 px-4 py-1.5 text-xs hover:bg-stone/20 transition ${hideCompleted ? 'text-brand-600 font-semibold' : 'text-textMain'}`}>
                                        <i className={`ph ph-eye${hideCompleted ? '-slash' : ''} text-sm ${hideCompleted ? 'text-brand-600' : 'text-textMuted'}`}></i>
                                        <span>{hideCompleted ? 'Show completed' : 'Hide completed'}</span>
                                    </button>
                                    <hr className="my-1 border-stone" />
                                    <button onClick={deleteProject} className="w-full flex items-center gap-2 px-4 py-1.5 text-xs text-terracotta hover:bg-terracotta/5 transition">
                                        <i className="ph ph-trash text-sm"></i>
                                        Delete project
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Quick add task */}
            <div className="mb-4">
                <div className="flex items-center gap-2 bg-white border border-stone rounded-2xl px-4 py-2.5">
                    <input
                        type="text"
                        value={quickTitle}
                        onChange={(e) => setQuickTitle(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && addQuickTask()}
                        placeholder="Add a task..."
                        className="flex-1 text-sm text-textMain outline-none ring-0 border-0 bg-transparent placeholder-textMuted focus:ring-0 focus:outline-none focus:border-0 focus:shadow-none"
                    />
                    <div className="flex-shrink-0">
                        <DatePicker value={quickDate} onChange={setQuickDate} iconMode label="Due Date" />
                    </div>
                    <div className="relative flex-shrink-0">
                        <button onClick={() => setQuickMenuOpen(!quickMenuOpen)} className="w-7 h-7 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition">
                            <i className="ph ph-caret-down text-xs"></i>
                        </button>
                        {quickMenuOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setQuickMenuOpen(false)} />
                                <div className="absolute top-full right-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1 w-56">
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Define priority</span></div>
                                    {['low', 'medium', 'high'].map((p) => (
                                        <button
                                            key={p}
                                            onClick={() => { setQuickPriority(p); setQuickMenuOpen(false); }}
                                            className={`w-full flex items-center gap-2 px-4 py-1.5 text-xs hover:bg-stone/20 transition capitalize ${
                                                quickPriority === p
                                                    ? p === 'low' ? 'text-green-600 font-semibold'
                                                    : p === 'medium' ? 'text-ochre font-semibold'
                                                    : 'text-terracotta font-semibold'
                                                    : 'text-textMain'
                                            }`}
                                        >
                                            <i className={`ph ph-flag text-sm ${p === 'low' ? 'text-green-500' : p === 'medium' ? 'text-ochre' : 'text-terracotta'}`}></i>
                                            {p}
                                        </button>
                                    ))}
                                    <hr className="my-1 border-stone" />
                                    <div className="px-4 py-1.5"><span className="text-[10px] font-semibold text-textMuted uppercase tracking-wider">Section & Tags</span></div>
                                    <div className="px-4 py-1.5">
                                        <label className="text-[10px] text-textMuted">Section</label>
                                        <select value={quickSectionId} onChange={(e) => setQuickSectionId(e.target.value)} className="w-full text-xs text-textMain border border-stone rounded-xl px-2 py-1 mt-0.5 outline-none focus:ring-1 focus:ring-ochre">
                                            <option value="">None</option>
                                            {sections.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                                        </select>
                                    </div>
                                    <div className="px-4 py-1.5">
                                        <label className="text-[10px] text-textMuted">Tags</label>
                                        <div className="flex flex-wrap gap-1 mt-1">
                                            {tags.map((tag) => (
                                                <label key={tag.id} className="flex items-center gap-1 cursor-pointer" onClick={(e) => e.stopPropagation()}>
                                                    <input type="checkbox" checked={quickTagIds.includes(tag.id)} onChange={() => toggleQuickTag(tag.id)} className="rounded border-stone text-brand-600 focus:ring-brand-500" />
                                                    <span className="text-[10px] px-1.5 py-0.5 rounded-full text-white" style={{ backgroundColor: tag.color }}>{tag.name}</span>
                                                </label>
                                            ))}
                                            {tags.length === 0 && <span className="text-[10px] text-textMuted">No tags</span>}
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Task List */}
            <div className="space-y-4">
                {groupBy === 'section' && (
                    <div className="space-y-4">
                        {sections.map((section, idx) => (
                            <div key={section.id}>
                                <div className="flex items-center gap-2 px-1 mb-3 group/section">
                                    <button onClick={() => toggleSection(section.id)} className="flex items-center gap-2 flex-1 min-w-0">
                                        <i className={`ph ph-caret-right text-sm text-textMuted transition-transform duration-200 flex-shrink-0 ${collapsed[section.id] ? 'rotate-90' : ''}`}></i>
                                        {editingSectionId === section.id ? (
                                            <input
                                                value={editingSectionName}
                                                autoFocus
                                                onChange={(e) => setEditingSectionName(e.target.value)}
                                                onKeyDown={(e) => e.key === 'Enter' && renameSection(section.id)}
                                                onBlur={() => renameSection(section.id)}
                                                onClick={(e) => e.stopPropagation()}
                                                className="text-sm font-semibold text-ink bg-white border border-stone rounded-xl px-2 py-0.5 outline-none ring-1 ring-ochre"
                                            />
                                        ) : (
                                            <span className="text-sm font-semibold text-ink truncate">{section.name}</span>
                                        )}
                                        <span className="text-xs text-textMuted rounded-full border border-stone px-2 py-0.5 font-medium flex-shrink-0">{sectionCount(section.id)}</span>
                                    </button>

                                    <div className="relative flex-shrink-0 opacity-0 group-hover/section:opacity-100 transition">
                                        <button onClick={() => setSectionMenuId(sectionMenuId === section.id ? null : section.id)} className="w-7 h-7 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink transition">
                                            <i className="ph ph-dots-three-vertical text-xs"></i>
                                        </button>
                                        {sectionMenuId === section.id && (
                                            <>
                                                <div className="fixed inset-0 z-40" onClick={() => setSectionMenuId(null)} />
                                                <div className="absolute top-full right-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1 w-44">
                                                    <button onClick={() => { setEditingSectionId(section.id); setEditingSectionName(section.name); setSectionMenuId(null); }} className="w-full text-left px-4 py-2 text-xs text-textMain hover:bg-stone/20 transition">Rename</button>
                                                    <button onClick={() => { setSectionMenuId(null); openNewTask(section.id); }} className="w-full text-left px-4 py-2 text-xs text-textMain hover:bg-stone/20 transition">Add Task</button>
                                                    <hr className="my-1 border-stone" />
                                                    <button onClick={() => { setSectionModalMode('above'); setSectionModalParentId(section.id); setSectionModalName(''); setSectionModalOpen(true); setSectionMenuId(null); }} className="w-full text-left px-4 py-2 text-xs text-textMain hover:bg-stone/20 transition">Add Section Above</button>
                                                    <button onClick={() => { setSectionModalMode('below'); setSectionModalParentId(section.id); setSectionModalName(''); setSectionModalOpen(true); setSectionMenuId(null); }} className="w-full text-left px-4 py-2 text-xs text-textMain hover:bg-stone/20 transition">Add Section Below</button>
                                                    <hr className="my-1 border-stone" />
                                                    <button onClick={() => { setMoveSectionId(section.id); setMoveProjectId(''); setMoveSectionTarget(''); setSectionMenuId(null); }} className="w-full text-left px-4 py-2 text-xs text-textMain hover:bg-stone/20 transition">Move to...</button>
                                                    <hr className="my-1 border-stone" />
                                                    <button onClick={() => { setDeleteSectionId(section.id); setDeleteSectionTarget(''); setSectionMenuId(null); }} className="w-full text-left px-4 py-2 text-xs text-terracotta hover:bg-terracotta/5 transition">Delete</button>
                                                </div>
                                            </>
                                        )}
                                    </div>

                                    <button onClick={() => openNewTask(section.id)} className="opacity-0 group-hover/section:opacity-100 transition w-7 h-7 rounded-full border border-stone flex items-center justify-center text-textMuted hover:text-ink" title="Add task to section">
                                        <i className="ph ph-plus text-xs"></i>
                                    </button>
                                </div>

                                {!collapsed[section.id] && (
                                    <div ref={(el) => { sectionRefs.current[section.id] = el; }} className="sortable-tasks space-y-3" data-section={section.id}>
                                        {filteredTasks.filter((t) => String(t.section_id) === String(section.id)).map(renderTaskRow)}
                                    </div>
                                )}
                            </div>
                        ))}

                        {filteredTasks.filter((t) => !t.section_id).length > 0 && (
                            <div>
                                <div className="px-1 py-2.5"><span className="text-sm font-semibold text-textMuted">No Section</span></div>
                                <div ref={(el) => { sectionRefs.current.none = el; }} className="sortable-tasks space-y-3" data-section="">
                                    {filteredTasks.filter((t) => !t.section_id).map(renderTaskRow)}
                                </div>
                            </div>
                        )}

                        <div className="border-t border-stone pt-3">
                            {!addingSection ? (
                                <button onClick={() => setAddingSection(true)} className="w-full flex items-center gap-3 px-1 py-2 text-sm text-textMuted hover:text-ink transition">
                                    <i className="ph ph-plus-circle text-base"></i>
                                    Add Section
                                </button>
                            ) : (
                                <div className="px-1 py-1">
                                    <input
                                        autoFocus
                                        value={newSectionName}
                                        onChange={(e) => setNewSectionName(e.target.value)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') createSection();
                                            if (e.key === 'Escape') { setAddingSection(false); setNewSectionName(''); }
                                        }}
                                        onBlur={() => { if (newSectionName.trim()) createSection(); else setAddingSection(false); }}
                                        type="text"
                                        placeholder="Section name..."
                                        className="w-full text-sm font-medium text-ink outline-none ring-0 border-0 bg-transparent placeholder-textMuted"
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {groupBy !== 'section' && taskGroups && (
                    <div className="space-y-4">
                        {taskGroups.map((group) => (
                            <div key={group.key ?? 'all'}>
                                {group.label && (
                                    <div className="px-1 py-1">
                                        <span className="text-sm font-semibold text-textMuted">{group.label}</span>
                                    </div>
                                )}
                                <div className="space-y-3">
                                    {group.tasks.map(renderTaskRow)}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* New Task Panel */}
            {newOpen && (
                <>
                    <div className="fixed inset-0 bg-ink/20 z-40" onClick={() => setNewOpen(false)} />
                    <div className="fixed top-0 right-0 h-full w-full max-w-md bg-paper border-l border-stone z-50 flex flex-col">
                        <div className="flex items-center gap-3 px-6 py-3 border-b border-stone flex-shrink-0">
                            <button onClick={() => setNewOpen(false)} className="text-textMuted hover:text-ink transition">
                                <i className="ph ph-x text-lg"></i>
                            </button>
                            <div className="w-px h-4 bg-stone" />

                            <div className="relative">
                                <button onClick={() => setNewSectionDropdownOpen(!newSectionDropdownOpen)} className="flex items-center gap-1.5 text-xs text-textMuted hover:text-ink px-2 py-1 rounded-xl hover:bg-stone/20 transition">
                                    <i className="ph ph-list text-sm"></i>
                                    <span>{sectionName(newSectionId)}</span>
                                    <i className="ph ph-caret-down text-xs text-textMuted"></i>
                                </button>
                                {newSectionDropdownOpen && (
                                    <>
                                        <div className="fixed inset-0 z-40" onClick={() => setNewSectionDropdownOpen(false)} />
                                        <div className="absolute top-full left-0 mt-1 bg-white border border-stone rounded-2xl shadow-floating z-50 py-1 w-40">
                                            {sections.map((s) => (
                                                <button key={s.id} onClick={() => { setNewSectionId(s.id); setNewSectionDropdownOpen(false); }} className={`w-full text-left px-4 py-2 text-xs hover:bg-stone/20 transition ${newSectionId === s.id ? 'text-brand-600 font-semibold' : 'text-textMain'}`}>
                                                    {s.name}
                                                </button>
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>

                            <div className="flex-1" />
                            <DatePicker value={newDate} onChange={setNewDate} iconMode label="Due Date" />
                            <div className="flex-1" />
                            <PriorityPicker value={newPriority} onChange={setNewPriority} />
                        </div>

                        <div className="flex flex-col flex-1 overflow-y-auto">
                            <div className="px-6 py-5 flex-1">
                                <input
                                    type="text"
                                    value={newTitle}
                                    onChange={(e) => setNewTitle(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && createTask()}
                                    className="w-full text-lg font-serif font-semibold text-ink outline-none ring-0 border-0 bg-transparent placeholder-textMuted"
                                    placeholder="Task title..."
                                    autoFocus
                                />
                            </div>
                            <div className="px-6 py-4 border-t border-stone">
                                <button onClick={createTask} className="w-full bg-ink hover:bg-ink/90 text-paper text-sm font-medium py-2.5 rounded-2xl transition">
                                    Create Task
                                </button>
                            </div>
                        </div>
                    </div>
                </>
            )}

            {/* Edit Panel */}
            {editTask && (
                <TaskEditPanel task={editTask} tags={tags} members={members} onClose={() => setEditTask(null)} onTaskUpdate={updateTask} />
            )}

            {/* Delete Section Modal */}
            {deleteSectionId && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-ink/20">
                    <div className="bg-white rounded-[2rem] shadow-floating w-full max-w-sm p-6">
                        <h3 className="text-lg font-serif font-semibold text-ink mb-2">Delete Section</h3>
                        <p className="text-sm text-textMuted mb-4">Tasks in this section will be moved to:</p>
                        <select value={deleteSectionTarget} onChange={(e) => setDeleteSectionTarget(e.target.value)} className="w-full text-sm border border-stone rounded-2xl px-3 py-2 outline-none focus:ring-1 focus:ring-ochre mb-5 text-textMain">
                            {sections.filter((s) => s.id !== deleteSectionId).map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                        </select>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteSectionId(null)} className="px-4 py-2 text-sm text-textMain hover:bg-stone/20 rounded-2xl transition">Cancel</button>
                            <button onClick={confirmDeleteSection} className="px-4 py-2 text-sm text-white bg-terracotta hover:bg-terracotta/90 rounded-2xl transition">Delete</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Move Section Modal */}
            {moveSectionId && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-ink/20">
                    <div className="bg-white rounded-[2rem] shadow-floating w-full max-w-sm p-6">
                        <h3 className="text-lg font-serif font-semibold text-ink mb-2">Move Section</h3>
                        <p className="text-sm text-textMuted mb-1">All tasks will be moved to:</p>
                        <select value={moveProjectId} onChange={(e) => { setMoveProjectId(e.target.value); setMoveSectionTarget(''); }} className="w-full text-sm border border-stone rounded-2xl px-3 py-2 outline-none focus:ring-1 focus:ring-ochre mb-3 text-textMain">
                            <option value="">Select project...</option>
                            {projects.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                        </select>
                        {moveProjectId && (
                            <div className="mb-5">
                                <select value={moveSectionTarget} onChange={(e) => setMoveSectionTarget(e.target.value)} className="w-full text-sm border border-stone rounded-2xl px-3 py-2 outline-none focus:ring-1 focus:ring-ochre text-textMain">
                                    {(projects.find((p) => String(p.id) === String(moveProjectId))?.sections || []).map((s) => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                            </div>
                        )}
                        {!moveProjectId && <div className="mb-5" />}
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setMoveSectionId(null)} className="px-4 py-2 text-sm text-textMain hover:bg-stone/20 rounded-2xl transition">Cancel</button>
                            <button onClick={confirmMoveSection} disabled={!moveProjectId} className="px-4 py-2 text-sm text-white bg-ink hover:bg-ink/90 rounded-2xl transition disabled:opacity-40">Move</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Add Section Above/Below Modal */}
            {sectionModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center">
                    <div className="absolute inset-0 bg-ink/20" onClick={() => setSectionModalOpen(false)} />
                    <div className="relative bg-white rounded-[2rem] shadow-floating w-full max-w-sm mx-4">
                        <div className="px-6 pt-6 pb-4">
                            <h3 className="text-lg font-serif font-semibold text-ink text-center">
                                {sectionModalMode === 'above' ? 'Add Section Above' : 'Add Section Below'}
                            </h3>
                        </div>
                        <div className="px-6 pb-6 space-y-4">
                            <input
                                type="text"
                                autoFocus
                                value={sectionModalName}
                                onChange={(e) => setSectionModalName(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && submitSectionModal()}
                                placeholder="Section name..."
                                className="w-full text-sm border border-stone rounded-2xl px-3 py-2 outline-none focus:ring-1 focus:ring-ochre placeholder-textMuted"
                            />
                            <div className="flex gap-3">
                                <button onClick={() => setSectionModalOpen(false)} className="flex-1 text-sm font-medium py-2.5 rounded-2xl border border-stone text-textMain hover:bg-stone/20 transition">Cancel</button>
                                <button onClick={submitSectionModal} className="flex-1 text-sm font-medium py-2.5 rounded-2xl bg-ink hover:bg-ink/90 text-paper transition">Create</button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
