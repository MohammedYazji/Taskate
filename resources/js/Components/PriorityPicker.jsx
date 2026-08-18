import { useState, useEffect, useRef } from 'react';

const PRIORITIES = [
    { value: 'low', label: 'Low', color: 'text-green-500', bg: 'bg-green-50 text-green-600' },
    { value: 'medium', label: 'Medium', color: 'text-yellow-500', bg: 'bg-yellow-50 text-yellow-600' },
    { value: 'high', label: 'High', color: 'text-red-500', bg: 'bg-red-50 text-red-600' },
];

export default function PriorityPicker({ value, onChange }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        if (!open) return;
        const handleClick = (e) => {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, [open]);

    const current = PRIORITIES.find(p => p.value === value);

    return (
        <div ref={ref} className="relative">
            <button onClick={() => setOpen(!open)} className="p-1.5 rounded-lg transition hover:bg-gray-50">
                <svg className={`w-4 h-4 ${current?.color || 'text-gray-300'}`} viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                    <line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" strokeWidth="2" />
                </svg>
            </button>
            {open && (
                <div className="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 w-32">
                    {PRIORITIES.map(p => (
                        <button key={p.value} onClick={() => { onChange(p.value); setOpen(false); }}
                            className={`w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition ${
                                value === p.value ? `${p.bg} font-semibold` : 'text-gray-600'
                            }`}>
                            <svg className={`w-4 h-4 ${p.color}`} viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                                <line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" strokeWidth="2" />
                            </svg>
                            {p.label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
