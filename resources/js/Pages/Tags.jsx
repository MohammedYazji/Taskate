import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function Tags({ tags: initialTags }) {
    const [tags, setTags] = useState(initialTags);
    const [name, setName] = useState('');
    const [color, setColor] = useState('#7C3AED');
    const [errors, setErrors] = useState({});

    const handleCreate = (e) => {
        e.preventDefault();
        router.post('/tags', { name, color }, {
            onSuccess: (page) => {
                setTags(page.props.tags);
                setName('');
                setColor('#7C3AED');
                setErrors({});
            },
            onError: (err) => setErrors(err),
        });
    };

    const handleDelete = (tag) => {
        if (!confirm(`Delete the "${tag.name}" tag?`)) return;
        router.delete(`/tags/${tag.id}`, {
            onSuccess: (page) => setTags(page.props.tags),
        });
    };

    return (
        <div className="max-w-lg mx-auto px-6 py-8">
                <h1 className="text-2xl font-bold text-gray-900 mb-6">Tags</h1>

                <form onSubmit={handleCreate} className="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex items-end gap-3">
                    <div className="flex-1">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Name</label>
                        <input
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            required
                            maxLength={50}
                            placeholder="e.g. Design"
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        />
                        {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">Color</label>
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
                                maxLength={7}
                                pattern="^#[0-9A-Fa-f]{6}$"
                                className="w-22 border border-gray-200 rounded-lg px-2 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono"
                            />
                        </div>
                        {errors.color && <p className="text-xs text-red-500 mt-1">{errors.color}</p>}
                    </div>
                    <button type="submit" className="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition whitespace-nowrap">
                        Add Tag
                    </button>
                </form>

                <div className="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    {tags.length === 0 && (
                        <div className="px-5 py-10 text-center text-gray-400 text-sm">No tags yet — create your first one above!</div>
                    )}
                    {tags.map((tag) => (
                        <div key={tag.id} className="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition group">
                            <span className="w-4 h-4 rounded-full flex-shrink-0 ring-2 ring-white shadow-sm" style={{ backgroundColor: tag.color }} />
                            <span className="flex-1 text-sm text-gray-800">{tag.name}</span>
                            <span className="text-xs text-gray-400 font-mono">{tag.color}</span>
                            <button
                                onClick={() => handleDelete(tag)}
                                className="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                title="Delete tag"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    ))}
                </div>
        </div>
    );
}
