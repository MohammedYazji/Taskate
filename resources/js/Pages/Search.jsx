import { Head, router } from '@inertiajs/react';

export default function Search({ tasks, query }) {
    const toggleStatus = (task) => {
        router.patch(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true, preserveState: true });
    };

    return (
        <div className="max-w-4xl">
            <Head title="Search" />

            <div className="mb-6">
                <h1 className="text-2xl font-semibold text-gray-900">
                    {query ? `Search results for "${query}"` : 'Search'}
                </h1>
                <p className="text-sm text-gray-500 mt-1">{tasks.length} task{tasks.length !== 1 ? 's' : ''} found</p>
            </div>

            <div className="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                {tasks.length === 0 && (
                    <div className="px-5 py-10 text-center text-gray-400 text-sm">
                        {query ? `No tasks match "${query}"` : 'Type a query to search tasks'}
                    </div>
                )}
                {tasks.map((task) => (
                    <div key={task.id} className="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
                        <button
                            onClick={() => toggleStatus(task)}
                            className={`w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center cursor-pointer transition ${
                                task.status === 'done' ? 'bg-brand-500 border-brand-500 hover:bg-brand-600' : 'border-gray-300 hover:border-brand-400'
                            }`}
                        >
                            {task.status === 'done' && (
                                <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                </svg>
                            )}
                        </button>
                        <div className="flex-1 min-w-0">
                            <p className={`text-sm ${task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                                {task.title}
                            </p>
                            {task.project_name && (
                                <p className="text-xs text-gray-400 mt-0.5">{task.project_name}</p>
                            )}
                        </div>
                        <span className={`text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wide ${
                            task.priority === 'high' ? 'text-red-500 bg-red-50'
                                : task.priority === 'medium' ? 'text-orange-500 bg-orange-50'
                                : 'text-green-600 bg-green-50'
                        }`}>
                            {task.priority}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
