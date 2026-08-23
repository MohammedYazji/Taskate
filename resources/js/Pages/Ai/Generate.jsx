import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function Generate({ folders }) {
    const [topic, setTopic] = useState('');
    const [folderId, setFolderId] = useState('');
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const handleSubmit = (e) => {
        e.preventDefault();
        setLoading(true);
        router.post('/ai/generate', { topic, folder_id: folderId || null }, {
            onFinish: () => setLoading(false),
            onError: (err) => setErrors(err),
        });
    };

    return (
        <div className="max-w-2xl mx-auto px-6 py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-gray-900">AI Task Generator</h1>
                    <p className="text-sm text-gray-500 mt-1">Describe a topic and AI will break it down into actionable tasks</p>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <form onSubmit={handleSubmit}>
                        <div className="mb-5">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Topic / Project</label>
                            <textarea
                                value={topic}
                                onChange={(e) => setTopic(e.target.value)}
                                rows={4}
                                required
                                minLength={5}
                                maxLength={500}
                                className="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                                placeholder="e.g., Build a REST API for an e-commerce platform with user authentication, product management, and order processing..."
                            />
                            {errors.topic && <p className="text-xs text-red-500 mt-1">{errors.topic}</p>}
                        </div>

                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Folder <span className="text-gray-400 font-normal">(optional)</span></label>
                            <select
                                value={folderId}
                                onChange={(e) => setFolderId(e.target.value)}
                                className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent bg-white"
                            >
                                <option value="">No folder</option>
                                {folders.map((f) => (
                                    <option key={f.id} value={f.id}>{f.name}</option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-3 rounded-lg transition flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {loading ? 'Generating...' : 'Generate Tasks'}
                        </button>
                    </form>
                </div>
        </div>
    );
}
