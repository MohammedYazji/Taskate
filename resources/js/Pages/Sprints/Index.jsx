import { Head, Link, useForm, router } from '@inertiajs/react';

const STATUS_DOT = {
    active: 'bg-green-500',
    completed: 'bg-gray-400',
    planned: 'bg-yellow-500',
};

function fmt(dateStr) {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

export default function Index({ project, sprints, activeSprint }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        goal: '',
        start_date: '',
        end_date: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('projects.sprints.store', project.id), {
            onSuccess: () => reset(),
        });
    };

    const activate = (sprint) => {
        router.patch(route('sprints.activate', sprint.id));
    };

    const destroy = (sprint) => {
        if (!confirm('Delete this sprint? Tasks will move to backlog.')) return;
        router.delete(route('sprints.destroy', sprint.id));
    };

    return (
        <div className="max-w-3xl">
            <Head title={`${project.name} - Sprints`} />

            <div className="mb-6">
                <h1 className="text-2xl font-semibold text-gray-900">{project.name}</h1>
                <p className="text-sm text-gray-500">Sprints</p>
            </div>

            {activeSprint && (
                <div className="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                    <div className="flex items-center gap-2">
                        <span className="w-2 h-2 bg-green-500 rounded-full" />
                        <h2 className="font-semibold text-gray-900">{activeSprint.name}</h2>
                        <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                    </div>
                    {activeSprint.goal && <p className="text-sm text-gray-500 mt-1">{activeSprint.goal}</p>}
                    <p className="text-xs text-gray-400 mt-1">{fmt(activeSprint.start_date)} - {fmt(activeSprint.end_date)}</p>
                </div>
            )}

            <div className="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                {sprints.length === 0 && (
                    <div className="px-5 py-10 text-center text-gray-400 text-sm">
                        No sprints yet, create your first one!
                    </div>
                )}
                {sprints.map((sprint) => (
                    <div key={sprint.id} className="flex items-center justify-between px-5 py-4">
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2">
                                <span className={`w-1.5 h-1.5 rounded-full ${STATUS_DOT[sprint.status] || 'bg-gray-300'}`} />
                                <h3 className="text-sm font-medium text-gray-900">{sprint.name}</h3>
                                <span className="text-xs text-gray-400">{sprint.status.charAt(0).toUpperCase() + sprint.status.slice(1)}</span>
                            </div>
                            {sprint.goal && <p className="text-xs text-gray-500 mt-0.5">{sprint.goal}</p>}
                            <p className="text-xs text-gray-400 mt-0.5">{fmt(sprint.start_date)} - {fmt(sprint.end_date)}</p>
                        </div>
                        <div className="flex items-center gap-2 flex-shrink-0">
                            {sprint.status !== 'active' && sprint.status !== 'completed' && (
                                <button onClick={() => activate(sprint)} className="text-xs text-green-600 hover:text-green-700 font-medium">
                                    Activate
                                </button>
                            )}
                            <button onClick={() => destroy(sprint)} className="text-xs text-red-500 hover:text-red-600">
                                Delete
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-6 bg-white rounded-xl border border-gray-200 p-5">
                <h3 className="font-semibold text-gray-900 mb-4">New Sprint</h3>
                <form onSubmit={submit}>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                type="text"
                                required
                                maxLength="100"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500"
                            />
                            {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Goal (optional)</label>
                            <textarea
                                rows="2"
                                value={data.goal}
                                onChange={(e) => setData('goal', e.target.value)}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input
                                    type="date"
                                    required
                                    value={data.start_date}
                                    onChange={(e) => setData('start_date', e.target.value)}
                                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500"
                                />
                                {errors.start_date && <p className="text-xs text-red-500 mt-1">{errors.start_date}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input
                                    type="date"
                                    required
                                    value={data.end_date}
                                    onChange={(e) => setData('end_date', e.target.value)}
                                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500"
                                />
                                {errors.end_date && <p className="text-xs text-red-500 mt-1">{errors.end_date}</p>}
                            </div>
                        </div>
                        <button type="submit" disabled={processing} className="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            Create Sprint
                        </button>
                    </div>
                </form>
            </div>

            <div className="mt-4">
                <Link href={route('projects.index')} className="text-sm text-brand-500 hover:text-brand-600">
                    &larr; Back to Projects
                </Link>
            </div>
        </div>
    );
}
