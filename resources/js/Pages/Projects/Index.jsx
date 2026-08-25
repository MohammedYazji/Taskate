import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ProjectsIndexSkeleton } from '@/Components/Skeleton';
import { useLoading } from '@/Components/LoadingContext';

export default function Index({ projects }) {
    const [open, setOpen] = useState(false);
    const [name, setName] = useState('');
    const [color, setColor] = useState('#7C3AED');
    const [description, setDescription] = useState('');
    const { loading } = useLoading();

    const createProject = (e) => {
        e.preventDefault();
        router.post('/projects', { name, color, description }, {
            onSuccess: () => {
                setOpen(false);
                setName('');
                setColor('#7C3AED');
                setDescription('');
            },
        });
    };

    if (loading) return <ProjectsIndexSkeleton />;

    return (
        <div className="max-w-5xl">
            <Head title="Projects" />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-gray-900">Projects</h1>
                <button
                    onClick={() => setOpen(true)}
                    className="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2"
                >
                    <span className="text-lg leading-none">+</span> New Project
                </button>
            </div>

            {projects.length === 0 ? (
                <div className="text-center py-16">
                    <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7h4v4H3zM3 14h4v4H3zM10 7h11M10 12h11M10 17h11" />
                        </svg>
                    </div>
                    <h2 className="text-lg font-semibold text-gray-700 mb-1">No projects yet</h2>
                    <p className="text-sm text-gray-400">Create a project to organize your tasks</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {projects.map((project) => {
                        const pct = project.tasks_count > 0 ? Math.round((project.completed_tasks_count / project.tasks_count) * 100) : 0;
                        return (
                            <Link
                                key={project.id}
                                href={route('projects.show', project.id)}
                                className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-gray-300 transition block group"
                            >
                                <div className="flex items-start justify-between mb-3">
                                    <div className="flex items-center gap-2">
                                        <span className="w-3.5 h-3.5 rounded-full flex-shrink-0" style={{ backgroundColor: project.color }} />
                                        <h3 className="font-semibold text-gray-900 truncate max-w-[200px]">{project.name}</h3>
                                    </div>
                                    <span className="text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition">
                                        {project.updated_at}
                                    </span>
                                </div>

                                {project.description && (
                                    <p className="text-xs text-gray-500 mb-3 line-clamp-2">{project.description}</p>
                                )}

                                <div>
                                    <div className="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                                        <span>{project.completed_tasks_count}/{project.tasks_count} tasks</span>
                                        <span>{pct}%</span>
                                    </div>
                                    <div className="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div className="h-full bg-brand-500 rounded-full transition-all" style={{ width: `${pct}%` }} />
                                    </div>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            )}

            {open && (
                <>
                    <div className="fixed inset-0 bg-black/30 z-40" onClick={() => setOpen(false)} />
                    <div className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">New Project</h2>
                            <button onClick={() => setOpen(false)} className="text-gray-400 hover:text-gray-600 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={createProject} className="flex flex-col flex-1 overflow-y-auto">
                            <div className="px-6 py-5 space-y-5 flex-1">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Name <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        required
                                        maxLength="100"
                                        value={name}
                                        onChange={(e) => setName(e.target.value)}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                        placeholder="Project name..."
                                        autoFocus
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                    <div className="flex items-center gap-1.5">
                                        <input
                                            type="color"
                                            value={color}
                                            onChange={(e) => setColor(e.target.value)}
                                            className="w-9 h-9 rounded-lg border border-gray-200 cursor-pointer p-0.5"
                                        />
                                        <input
                                            type="text"
                                            value={color}
                                            onChange={(e) => setColor(e.target.value)}
                                            maxLength="7"
                                            pattern="^#[0-9A-Fa-f]{6}$"
                                            required
                                            className="w-24 border border-gray-200 rounded-lg px-2 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea
                                        rows="3"
                                        maxLength="500"
                                        value={description}
                                        onChange={(e) => setDescription(e.target.value)}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                                        placeholder="Project description..."
                                    />
                                </div>
                            </div>

                            <div className="px-6 py-4 border-t border-gray-200">
                                <button
                                    type="submit"
                                    className="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 rounded-lg transition"
                                >
                                    Create Project
                                </button>
                            </div>
                        </form>
                    </div>
                </>
            )}
        </div>
    );
}
