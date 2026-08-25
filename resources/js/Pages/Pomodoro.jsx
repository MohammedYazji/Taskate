import { useState, useEffect, useRef, useCallback } from 'react';
import { router, usePage } from '@inertiajs/react';
import { PomodoroSkeleton } from '@/Components/Skeleton';
import { useLoading } from '@/Components/LoadingContext';

const SESSION_TYPES = {
    work: { label: 'Focus', duration: 25 * 60, color: 'brand', icon: '🔥' },
    short_break: { label: 'Short Break', duration: 5 * 60, color: 'green', icon: '☕' },
    long_break: { label: 'Long Break', duration: 15 * 60, color: 'blue', icon: '🌴' },
};

function CircularProgress({ progress, size = 280, strokeWidth = 6, color = '#14B8A6' }) {
    const radius = (size - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (progress * circumference);

    return (
        <svg width={size} height={size} className="transform -rotate-90">
            <circle cx={size / 2} cy={size / 2} r={radius}
                fill="none" stroke="#f3f4f6" strokeWidth={strokeWidth} />
            <circle cx={size / 2} cy={size / 2} r={radius}
                fill="none" stroke={color} strokeWidth={strokeWidth}
                strokeLinecap="round"
                strokeDasharray={circumference}
                strokeDashoffset={offset}
                className="transition-all duration-1000 ease-linear" />
        </svg>
    );
}

function SessionTypeSelector({ active, onChange }) {
    return (
        <div className="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
            {Object.entries(SESSION_TYPES).map(([key, type]) => (
                <button key={key} onClick={() => onChange(key)}
                    className={`flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg transition ${
                        active === key
                            ? 'bg-white text-brand-600 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'
                    }`}>
                    <span>{type.icon}</span>
                    <span>{type.label}</span>
                </button>
            ))}
        </div>
    );
}

export default function Pomodoro({ tasks, tags }) {
    const { loading } = useLoading();
    const [sessionType, setSessionType] = useState('work');
    const [timeLeft, setTimeLeft] = useState(SESSION_TYPES.work.duration);
    const [isRunning, setIsRunning] = useState(false);
    const [selectedTaskId, setSelectedTaskId] = useState(null);
    const [note, setNote] = useState('');
    const [stats, setStats] = useState({ work_count: 0, short_break_count: 0, long_break_count: 0, total_minutes: 0, sessions: [] });
    const [sessions, setSessions] = useState([]);
    const intervalRef = useRef(null);
    const audioRef = useRef(null);

    const selectedTask = tasks.find(t => t.id == selectedTaskId);
    const totalDuration = SESSION_TYPES[sessionType].duration;
    const progress = 1 - (timeLeft / totalDuration);
    const minutes = String(Math.floor(timeLeft / 60)).padStart(2, '0');
    const seconds = String(timeLeft % 60).padStart(2, '0');

    const colorMap = { brand: '#14B8A6', green: '#22c55e', blue: '#3b82f6' };

    useEffect(() => {
        fetchStats();
    }, []);

    useEffect(() => {
        if (isRunning && timeLeft > 0) {
            intervalRef.current = setInterval(() => {
                setTimeLeft(t => t - 1);
            }, 1000);
        } else if (timeLeft === 0 && isRunning) {
            clearInterval(intervalRef.current);
            setIsRunning(false);
            completeSession();
        }
        return () => clearInterval(intervalRef.current);
    }, [isRunning, timeLeft]);

    const fetchStats = async () => {
        try {
            const res = await fetch('/pomodoro/stats');
            const data = await res.json();
            setStats(data);
            setSessions(data.sessions || []);
        } catch (e) {}
    };

    const completeSession = () => {
        const duration = totalDuration - timeLeft;
        router.post('/pomodoro/sessions', {
            task_id: selectedTaskId || null,
            note: note || null,
            type: sessionType,
            duration: duration,
        }, { preserveScroll: true, onSuccess: () => fetchStats() });
    };

    const toggleTimer = () => {
        if (isRunning) {
            clearInterval(intervalRef.current);
        }
        setIsRunning(!isRunning);
    };

    const resetTimer = () => {
        clearInterval(intervalRef.current);
        setIsRunning(false);
        setTimeLeft(totalDuration);
    };

    const switchSession = (type) => {
        clearInterval(intervalRef.current);
        setIsRunning(false);
        setSessionType(type);
        setTimeLeft(SESSION_TYPES[type].duration);
    };

    const skipSession = () => {
        clearInterval(intervalRef.current);
        setIsRunning(false);
        completeSession();
        const nextType = sessionType === 'work' ? 'short_break' : 'work';
        switchSession(nextType);
    };

    if (loading) return <PomodoroSkeleton />;

    return (
        <div className="max-w-5xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-2xl font-bold text-gray-900">Focus Timer</h1>
                <p className="text-sm text-gray-500 mt-1">Stay focused, take breaks, be productive</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Timer */}
                <div className="lg:col-span-2">
                    <div className="bg-white rounded-2xl border border-gray-200 p-8 flex flex-col items-center">
                        <SessionTypeSelector active={sessionType} onChange={switchSession} />

                        <div className="relative my-10">
                            <CircularProgress progress={progress} color={colorMap[SESSION_TYPES[sessionType].color]} />
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-5xl font-bold text-gray-900 tabular-nums">
                                    {minutes}:{seconds}
                                </span>
                                <span className="text-sm text-gray-400 mt-1">
                                    {SESSION_TYPES[sessionType].label}
                                </span>
                            </div>
                        </div>

                        {/* Controls */}
                        <div className="flex items-center gap-4">
                            <button onClick={resetTimer}
                                className="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </button>
                            <button onClick={toggleTimer}
                                className={`w-16 h-16 rounded-full flex items-center justify-center text-white shadow-lg transition active:scale-95 ${
                                    isRunning ? 'bg-orange-500 hover:bg-orange-600' : 'bg-brand-500 hover:bg-brand-600'
                                }`}>
                                {isRunning ? (
                                    <svg className="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1" /><rect x="14" y="4" width="4" height="16" rx="1" /></svg>
                                ) : (
                                    <svg className="w-7 h-7 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                )}
                            </button>
                            <button onClick={skipSession}
                                className="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                            </button>
                        </div>

                        {/* Task selector */}
                        <div className="w-full mt-8">
                            <label className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Focus Task</label>
                            <select value={selectedTaskId || ''} onChange={(e) => setSelectedTaskId(e.target.value || null)}
                                className="w-full mt-1.5 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                <option value="">No task (free focus)</option>
                                {tasks.map(task => (
                                    <option key={task.id} value={task.id}>{task.title}</option>
                                ))}
                            </select>
                            {selectedTask && (
                                <div className="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                    <span className={`w-2 h-2 rounded-full ${
                                        selectedTask.priority === 'high' ? 'bg-red-400' :
                                        selectedTask.priority === 'medium' ? 'bg-yellow-400' : 'bg-green-400'
                                    }`} />
                                    {selectedTask.project_name && (
                                        <span className="bg-gray-100 px-1.5 py-0.5 rounded">{selectedTask.project_name}</span>
                                    )}
                                    {selectedTask.due_date && (
                                        <span>Due {new Date(selectedTask.due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</span>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-4">
                    {/* Today Stats */}
                    <div className="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Today</h3>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="bg-brand-50 rounded-xl p-3 text-center">
                                <p className="text-2xl font-bold text-brand-600">{stats.work_count}</p>
                                <p className="text-[10px] text-brand-500 font-medium mt-0.5">Focus Sessions</p>
                            </div>
                            <div className="bg-green-50 rounded-xl p-3 text-center">
                                <p className="text-2xl font-bold text-green-600">{stats.total_minutes}</p>
                                <p className="text-[10px] text-green-500 font-medium mt-0.5">Minutes</p>
                            </div>
                            <div className="bg-blue-50 rounded-xl p-3 text-center">
                                <p className="text-2xl font-bold text-blue-600">{stats.short_break_count}</p>
                                <p className="text-[10px] text-blue-500 font-medium mt-0.5">Short Breaks</p>
                            </div>
                            <div className="bg-purple-50 rounded-xl p-3 text-center">
                                <p className="text-2xl font-bold text-purple-600">{stats.long_break_count}</p>
                                <p className="text-[10px] text-purple-500 font-medium mt-0.5">Long Breaks</p>
                            </div>
                        </div>
                    </div>

                    {/* Session History */}
                    <div className="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Session History</h3>
                        {sessions.length > 0 ? (
                            <div className="space-y-2">
                                {sessions.map((session, i) => (
                                    <div key={i} className="flex items-center gap-2.5 py-1.5">
                                        <div className={`w-2 h-2 rounded-full flex-shrink-0 ${
                                            session.type === 'work' ? 'bg-brand-400' :
                                            session.type === 'short_break' ? 'bg-green-400' : 'bg-blue-400'
                                        }`} />
                                        <span className="text-xs text-gray-700 flex-1 truncate">
                                            {session.type === 'work' ? 'Focus' :
                                             session.type === 'short_break' ? 'Short Break' : 'Long Break'}
                                            {session.note && ` — ${session.note}`}
                                        </span>
                                        <span className="text-[10px] text-gray-400 flex-shrink-0">
                                            {Math.round(session.duration / 60)}m
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-xs text-gray-400 text-center py-4">No sessions today</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
