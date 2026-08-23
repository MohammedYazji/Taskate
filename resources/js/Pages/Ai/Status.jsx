import { Link } from '@inertiajs/react';

export default function Status({ generation }) {
    return (
        <div className="max-w-2xl mx-auto px-6 py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-gray-900">AI Task Generator</h1>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div className="flex flex-col items-center gap-3">
                        <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <p className="text-sm font-medium text-red-700">Generation failed</p>
                        {generation.error && (
                            <p className="text-xs text-red-500 max-w-md">{generation.error}</p>
                        )}
                        <Link href="/ai/generate" className="text-sm text-brand-600 hover:text-brand-700 font-medium mt-2">
                            Try again
                        </Link>
                    </div>
                </div>

                <div className="mt-4 bg-gray-50 rounded-lg px-4 py-3">
                    <p className="text-xs text-gray-400">Topic: <span className="text-gray-600">{generation.topic}</span></p>
                </div>
        </div>
    );
}
