import { useState } from 'react';
import { router, Link } from '@inertiajs/react';

export default function Review({ sections: initialSections, projectName: initialProjectName, topic, folderId, folders }) {
    const [sections, setSections] = useState(
        initialSections.map(s => ({
            ...s,
            approved: true,
            tasks: (s.tasks || []).map(t => ({
                ...t,
                approved: true,
                editing: false,
                subtasks: t.subtasks || [],
            })),
        }))
    );
    const [projectName, setProjectName] = useState(initialProjectName);
    const [selectedFolder, setSelectedFolder] = useState(folderId || '');
    const [submitting, setSubmitting] = useState(false);

    const toggleSection = (si) => {
        setSections(prev => prev.map((s, i) => i === si ? { ...s, approved: !s.approved } : s));
    };

    const toggleTask = (si, ti) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? { ...t, approved: !t.approved } : t),
        } : s));
    };

    const toggleSubtask = (si, ti, subIdx) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? {
                ...t,
                subtasks: t.subtasks.map((sub, k) => k === subIdx ? { ...sub, approved: sub.approved ? 0 : 1 } : sub),
            } : t),
        } : s));
    };

    const updateTaskField = (si, ti, field, value) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? { ...t, [field]: value } : t),
        } : s));
    };

    const updateSubtaskTitle = (si, ti, subIdx, title) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? {
                ...t,
                subtasks: t.subtasks.map((sub, k) => k === subIdx ? { ...sub, title } : sub),
            } : t),
        } : s));
    };

    const addSubtask = (si, ti) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? {
                ...t,
                subtasks: [...t.subtasks, { title: '', approved: 1 }],
            } : t),
        } : s));
    };

    const removeSubtask = (si, ti, subIdx) => {
        setSections(prev => prev.map((s, i) => i === si ? {
            ...s,
            tasks: s.tasks.map((t, j) => j === ti ? {
                ...t,
                subtasks: t.subtasks.filter((_, k) => k !== subIdx),
            } : t),
        } : s));
    };

    const handleApprove = (e) => {
        e.preventDefault();
        setSubmitting(true);
        const form = new FormData();
        form.append('project_name', projectName);
        if (selectedFolder) form.append('folder_id', selectedFolder);
        sections.forEach((section, si) => {
            form.append(`sections[${si}][name]`, section.name);
            form.append(`sections[${si}][approved]`, section.approved ? '1' : '0');
            section.tasks.forEach((task, ti) => {
                form.append(`sections[${si}][tasks][${ti}][title]`, task.title);
                form.append(`sections[${si}][tasks][${ti}][description]`, task.description || '');
                form.append(`sections[${si}][tasks][${ti}][priority]`, task.priority);
                form.append(`sections[${si}][tasks][${ti}][due_date]`, task.due_date || '');
                form.append(`sections[${si}][tasks][${ti}][approved]`, task.approved ? '1' : '0');
                task.subtasks.forEach((sub, subIdx) => {
                    form.append(`sections[${si}][tasks][${ti}][subtasks][${subIdx}][title]`, sub.title);
                    form.append(`sections[${si}][tasks][${ti}][subtasks][${subIdx}][approved]`, sub.approved);
                });
            });
        });
        router.post('/ai/approve', form, {
            onFinish: () => setSubmitting(false),
        });
    };

    const priorityColor = (p) => {
        if (p === 'high') return 'text-red-600 bg-red-50 border-red-100';
        if (p === 'medium') return 'text-orange-500 bg-orange-50 border-orange-100';
        return 'text-green-600 bg-green-50 border-green-100';
    };

    return (
        <div className="max-w-3xl mx-auto px-6 py-8">
                <div className="mb-8">
                    <div className="flex items-center gap-3 mb-2">
                        <Link href="/ai/generate" className="text-gray-400 hover:text-gray-600 transition">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 className="text-2xl font-bold text-gray-900">Review AI Tasks</h1>
                    </div>
                    <p className="text-sm text-gray-500">Review, edit, and approve tasks for: <span className="font-medium text-gray-700">{topic}</span></p>
                </div>

                <form onSubmit={handleApprove}>
                    <div className="bg-white rounded-xl border border-gray-200 p-5 mb-6 space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input
                                type="text"
                                value={projectName}
                                onChange={(e) => setProjectName(e.target.value)}
                                required
                                className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                placeholder="Enter project name..."
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Folder <span className="text-gray-400 font-normal">(optional)</span></label>
                            <select
                                value={selectedFolder}
                                onChange={(e) => setSelectedFolder(e.target.value)}
                                className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent bg-white"
                            >
                                <option value="">No folder</option>
                                {folders.map((f) => (
                                    <option key={f.id} value={f.id}>{f.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {sections.map((section, si) => (
                        <div key={si} className="mb-4">
                            <div className="flex items-center gap-3 mb-2">
                                <input
                                    type="checkbox"
                                    checked={section.approved}
                                    onChange={() => toggleSection(si)}
                                    className="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                />
                                <input
                                    type="text"
                                    value={section.name}
                                    onChange={(e) => {
                                        const newSections = [...sections];
                                        newSections[si] = { ...newSections[si], name: e.target.value };
                                        setSections(newSections);
                                    }}
                                    required
                                    className="text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg px-2 py-1 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                />
                            </div>

                            <div className="space-y-2 ml-7">
                                {section.tasks.map((task, ti) => (
                                    <div key={ti} className="bg-white rounded-xl border border-gray-200 hover:border-brand-400/30 transition p-4">
                                        {!task.editing ? (
                                            <div className="flex items-start gap-3">
                                                <input
                                                    type="checkbox"
                                                    checked={task.approved}
                                                    onChange={() => toggleTask(si, ti)}
                                                    className="mt-1 w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                                />
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="text-sm font-semibold text-gray-900">{task.title}</h3>
                                                    {task.description && (
                                                        <pre className="text-xs text-gray-500 mt-1 whitespace-pre-wrap font-sans">{task.description}</pre>
                                                    )}
                                                    <span className={`text-xs font-medium px-2 py-0.5 rounded border mt-1 inline-block ${priorityColor(task.priority)}`}>
                                                        {task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}
                                                    </span>
                                                    {task.due_date && (
                                                        <span className="text-xs text-gray-400 ml-2">
                                                            {new Date(task.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                                        </span>
                                                    )}
                                                    {task.subtasks.length > 0 && (
                                                        <div className="mt-2 pl-1 space-y-0.5">
                                                            {task.subtasks.map((sub, subIdx) => (
                                                                <div key={subIdx} className="flex items-center gap-1.5">
                                                                    <svg className="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                                                    </svg>
                                                                    <span className="text-xs text-gray-500">{sub.title}</span>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    )}
                                                </div>
                                                <button type="button" onClick={() => updateTaskField(si, ti, 'editing', true)}
                                                    className="p-1 text-gray-300 hover:text-brand-500 hover:bg-brand-50 rounded transition">
                                                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="space-y-3">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-medium text-brand-500">Editing Task</span>
                                                    <button type="button" onClick={() => updateTaskField(si, ti, 'editing', false)}
                                                        className="text-xs text-gray-400 hover:text-gray-600 transition">Done</button>
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                                    <input type="text" value={task.title} required
                                                        onChange={(e) => updateTaskField(si, ti, 'title', e.target.value)}
                                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-700 mb-1">Description (Markdown)</label>
                                                    <textarea value={task.description || ''} rows={3}
                                                        onChange={(e) => updateTaskField(si, ti, 'description', e.target.value)}
                                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none font-mono" />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                                                    <select value={task.priority}
                                                        onChange={(e) => updateTaskField(si, ti, 'priority', e.target.value)}
                                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500">
                                                        <option value="low">Low</option>
                                                        <option value="medium">Medium</option>
                                                        <option value="high">High</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-700 mb-1">Due Date</label>
                                                    <input type="date" value={task.due_date || ''}
                                                        onChange={(e) => updateTaskField(si, ti, 'due_date', e.target.value)}
                                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-700 mb-2">Subtasks</label>
                                                    <div className="space-y-1.5">
                                                        {task.subtasks.map((sub, subIdx) => (
                                                            <div key={subIdx} className="flex items-center gap-2">
                                                                <input type="checkbox" checked={sub.approved == 1}
                                                                    onChange={() => toggleSubtask(si, ti, subIdx)}
                                                                    className="w-3.5 h-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                                                <input type="text" value={sub.title}
                                                                    onChange={(e) => updateSubtaskTitle(si, ti, subIdx, e.target.value)}
                                                                    placeholder={`Subtask ${subIdx + 1}`}
                                                                    className="flex-1 border border-gray-200 rounded px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" />
                                                                <button type="button" onClick={() => removeSubtask(si, ti, subIdx)}
                                                                    className="text-gray-300 hover:text-red-400 transition p-0.5">
                                                                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        ))}
                                                    </div>
                                                    <button type="button" onClick={() => addSubtask(si, ti)}
                                                        className="mt-1.5 text-xs text-brand-500 hover:text-brand-600 font-medium transition flex items-center gap-1">
                                                        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Add subtask
                                                    </button>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}

                    <div className="flex items-center gap-4 mt-6">
                        <button type="submit" disabled={submitting}
                            className="flex-1 bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-3 rounded-lg transition flex items-center justify-center gap-2 disabled:opacity-50">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {submitting ? 'Creating...' : 'Approve & Create Project'}
                        </button>
                        <Link href="/ai/generate"
                            className="px-6 py-3 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </Link>
                    </div>
                </form>
        </div>
    );
}
