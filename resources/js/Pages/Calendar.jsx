import { useState } from 'react';
import { Head, router, Link } from '@inertiajs/react';

const VIEWS = ['year', 'month', 'week', 'day', 'agenda', 'multi-day', 'multi-week'];
const WEEKDAYS_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const WEEKDAYS_MIN = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

function toDateStr(d) {
    return d.toISOString().slice(0, 10);
}

function parseDate(str) {
    return new Date(str + 'T00:00:00');
}

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

function monthName(m) {
    return new Date(2024, m).toLocaleString('en-US', { month: 'long' });
}

export default function Calendar({ view, current, tasks }) {
    const [editTask, setEditTask] = useState(null);

    const currentDate = parseDate(current);
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();
    const today = todayStr();

    const navigate = (nextView, nextDate) => {
        router.get(route('calendar'), { view: nextView, date: nextDate }, { preserveState: true, preserveScroll: true });
    };

    const goToday = () => navigate(view, today);

    const goPrev = () => {
        const d = new Date(currentDate);
        const offset = { year: -365, month: -30, week: -7, day: -1, agenda: -21, 'multi-day': -7, 'multi-week': -21 };
        d.setDate(d.getDate() + (offset[view] || -30));
        navigate(view, toDateStr(d));
    };

    const goNext = () => {
        const d = new Date(currentDate);
        const offset = { year: 365, month: 30, week: 7, day: 1, agenda: 21, 'multi-day': 7, 'multi-week': 21 };
        d.setDate(d.getDate() + (offset[view] || 30));
        navigate(view, toDateStr(d));
    };

    const goToDate = (dateStr) => navigate(view, dateStr);
    const switchView = (v) => navigate(v, current);

    const tasksOnDate = (dateStr) => tasks.filter((t) => t.due_date === dateStr);
    const hasTaskOnDate = (dateStr) => tasks.some((t) => t.due_date === dateStr);

    const headerLabel = (() => {
        const d = currentDate;
        if (view === 'day') return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        if (view === 'week' || view === 'agenda') {
            const end = new Date(d); end.setDate(end.getDate() + 6);
            return `${d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
        }
        if (view === 'year') return d.toLocaleDateString('en-US', { year: 'numeric' });
        if (view === 'multi-day') {
            const end = new Date(d); end.setDate(end.getDate() + 6);
            return `${d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
        }
        if (view === 'multi-week') {
            const end = new Date(d); end.setDate(end.getDate() + 20);
            return `${d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
        }
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    })();

    const yearDays = (m) => {
        const first = new Date(currentYear, m, 1).getDay();
        const last = new Date(currentYear, m + 1, 0).getDate();
        const days = [];
        for (let i = 0; i < first; i++) days.push(null);
        for (let d = 1; d <= last; d++) days.push(d);
        return days;
    };

    const monthWeeks = Math.ceil((new Date(currentYear, currentMonth, 1).getDay() + new Date(currentYear, currentMonth + 1, 0).getDate()) / 7);

    const monthCells = (() => {
        const first = new Date(currentYear, currentMonth, 1);
        const startDay = first.getDay();
        const start = new Date(first);
        start.setDate(start.getDate() - startDay);
        const cells = [];
        const total = monthWeeks * 7;
        for (let i = 0; i < total; i++) {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            cells.push({
                date: toDateStr(d),
                day: d.getDate(),
                currentMonth: d.getMonth() === currentMonth,
                isToday: toDateStr(d) === today,
            });
        }
        return cells;
    })();

    const buildDays = (count) => {
        const start = new Date(currentDate);
        const days = [];
        for (let i = 0; i < count; i++) {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            days.push({
                date: toDateStr(d),
                short: d.toLocaleDateString('en-US', { weekday: 'short' }),
                num: d.getDate(),
                isToday: toDateStr(d) === today,
            });
        }
        return days;
    };

    const weekDays = (() => {
        const start = new Date(currentDate);
        start.setDate(start.getDate() - start.getDay());
        const days = [];
        for (let i = 0; i < 7; i++) {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            days.push({
                date: toDateStr(d),
                short: d.toLocaleDateString('en-US', { weekday: 'short' }),
                num: d.getDate(),
                isToday: toDateStr(d) === today,
            });
        }
        return days;
    })();

    const multiDays = buildDays(7);

    const agendaWeeks = (() => {
        const start = new Date(currentDate);
        start.setDate(start.getDate() - start.getDay());
        const weeks = [];
        for (let w = 0; w < 3; w++) {
            const weekStart = new Date(start);
            weekStart.setDate(weekStart.getDate() + w * 7);
            const weekEnd = new Date(weekStart.getTime() + 6 * 86400000);
            const label = `${weekStart.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })} - ${weekEnd.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
            const days = [];
            for (let d = 0; d < 7; d++) {
                const dd = new Date(weekStart);
                dd.setDate(dd.getDate() + d);
                const dateStr = toDateStr(dd);
                days.push({
                    date: dateStr,
                    short: dd.toLocaleDateString('en-US', { weekday: 'short' }),
                    num: dd.getDate(),
                    isToday: dateStr === today,
                    tasks: tasksOnDate(dateStr),
                });
            }
            weeks.push({ label, days });
        }
        return weeks;
    })();

    const multiWeekCells = (() => {
        const start = new Date(currentDate);
        start.setDate(start.getDate() - start.getDay());
        const cells = [];
        for (let i = 0; i < 21; i++) {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            cells.push({
                date: toDateStr(d),
                day: d.getDate(),
                currentMonth: d.getMonth() === currentMonth,
                isToday: toDateStr(d) === today,
            });
        }
        return cells;
    })();

    const agendaHasTasks = agendaWeeks.some((w) => w.days.some((d) => d.tasks.length > 0));

    return (
        <div className="h-full flex flex-col">
            <Head title="Calendar" />

            {/* Toolbar */}
            <div className="flex items-center justify-between mb-4 flex-shrink-0">
                <div className="flex items-center gap-3">
                    <button onClick={goToday} className="text-xs font-medium border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition text-gray-600">
                        Today
                    </button>
                    <div className="flex items-center gap-1">
                        <button onClick={goPrev} className="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button onClick={goNext} className="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                    <h2 className="text-lg font-semibold text-gray-900">{headerLabel}</h2>
                </div>
                <div className="flex items-center bg-gray-100 rounded-lg p-0.5">
                    {VIEWS.map((v) => (
                        <button
                            key={v}
                            onClick={() => switchView(v)}
                            className={`px-3 py-1 text-xs font-medium rounded-md transition capitalize ${view === v ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            {v.replace('-', ' ')}
                        </button>
                    ))}
                </div>
            </div>

            {/* Calendar Content */}
            <div className="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

                {/* Year View */}
                {view === 'year' && (
                    <div className="flex-1 overflow-y-auto p-4">
                        <div className="grid grid-cols-4 gap-4">
                            {Array.from({ length: 12 }, (_, m) => (
                                <div key={m} className="border border-gray-100 rounded-lg p-3">
                                    <p className="text-xs font-semibold text-gray-700 mb-2">{monthName(m)}</p>
                                    <div className="grid grid-cols-7 gap-px text-center">
                                        {WEEKDAYS_MIN.map((d, i) => (
                                            <div key={i} className="text-[8px] text-gray-400">{d}</div>
                                        ))}
                                        {yearDays(m).map((day, idx) => {
                                            const dateStr = day ? `${currentYear}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}` : null;
                                            return (
                                                <div
                                                    key={idx}
                                                    onClick={() => day && goToDate(dateStr)}
                                                    className={`text-[9px] py-0.5 rounded cursor-pointer hover:bg-brand-50 transition ${
                                                        day && dateStr === current ? 'bg-brand-500 text-white font-bold'
                                                            : day && hasTaskOnDate(dateStr) ? 'font-semibold text-gray-700'
                                                            : !day ? 'text-gray-400' : ''
                                                    }`}
                                                >
                                                    {day || ''}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Month View */}
                {view === 'month' && (
                    <div className="flex-1 flex flex-col min-h-0">
                        <div className="grid grid-cols-7 border-b border-gray-100">
                            {WEEKDAYS_SHORT.map((d) => (
                                <div key={d} className="text-xs font-semibold text-gray-500 py-2 text-center">{d}</div>
                            ))}
                        </div>
                        <div className="grid grid-cols-7 flex-1 min-h-0" style={{ gridTemplateRows: `repeat(${monthWeeks}, 1fr)` }}>
                            {monthCells.map((cell, idx) => {
                                const cellTasks = tasksOnDate(cell.date);
                                return (
                                    <div
                                        key={idx}
                                        onClick={() => goToDate(cell.date)}
                                        className={`border-r border-b border-gray-100 p-1 overflow-hidden cursor-pointer hover:bg-gray-50/50 transition ${!cell.currentMonth ? 'bg-gray-50/30' : ''}`}
                                    >
                                        <div className="flex items-center justify-between mb-1">
                                            <span className={`text-xs w-6 h-6 flex items-center justify-center rounded-full ${
                                                cell.isToday ? 'bg-brand-500 text-white font-semibold'
                                                    : cell.currentMonth ? 'text-gray-700' : 'text-gray-300'
                                            }`}>{cell.day}</span>
                                        </div>
                                        <div className="space-y-0.5">
                                            {cellTasks.slice(0, 3).map((task) => (
                                                <div
                                                    key={task.id}
                                                    onClick={(e) => { e.stopPropagation(); setEditTask(task); }}
                                                    className="text-[10px] px-1.5 py-0.5 rounded truncate cursor-pointer hover:opacity-80 transition"
                                                    style={{ backgroundColor: task.project_color + '20', color: task.project_color }}
                                                >
                                                    {task.title}
                                                </div>
                                            ))}
                                            {cellTasks.length > 3 && (
                                                <div className="text-[9px] text-gray-400 px-1">+{cellTasks.length - 3} more</div>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Week View */}
                {view === 'week' && (
                    <div className="flex-1 flex flex-col min-h-0">
                        <div className="grid grid-cols-8 border-b border-gray-100">
                            <div className="text-xs text-gray-400 py-2 px-2" />
                            {weekDays.map((day) => (
                                <div key={day.date} className="text-center py-2 border-l border-gray-100">
                                    <div className="text-[10px] font-semibold text-gray-500 uppercase">{day.short}</div>
                                    <div className={`text-lg font-bold mt-0.5 ${day.isToday ? 'text-brand-500' : 'text-gray-800'}`}>{day.num}</div>
                                </div>
                            ))}
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            <div className="grid grid-cols-8 min-h-[600px]">
                                <div className="border-r border-gray-100">
                                    {Array.from({ length: 24 }, (_, h) => (
                                        <div key={h} className="h-12 border-b border-gray-50 px-2 flex items-start pt-0.5">
                                            <span className="text-[10px] text-gray-400">{String(h).padStart(2, '0')}:00</span>
                                        </div>
                                    ))}
                                </div>
                                {weekDays.map((day) => (
                                    <div key={day.date} className="border-l border-gray-100 relative">
                                        {Array.from({ length: 24 }, (_, h) => (
                                            <div key={h} className="h-12 border-b border-gray-50" />
                                        ))}
                                        {tasksOnDate(day.date).map((task, tIdx) => (
                                            <div
                                                key={task.id}
                                                onClick={() => setEditTask(task)}
                                                className="absolute left-0.5 right-0.5 px-1.5 py-1 rounded text-[10px] cursor-pointer hover:opacity-80 transition truncate z-10"
                                                style={{ top: (8 + tIdx) * 48, backgroundColor: task.project_color + '20', color: task.project_color }}
                                            >
                                                {task.title}
                                            </div>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Day View */}
                {view === 'day' && (
                    <div className="flex-1 flex flex-col min-h-0">
                        <div className="border-b border-gray-100 px-4 py-3">
                            <p className="text-sm font-semibold text-gray-900">
                                {currentDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}
                            </p>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            <div className="grid grid-cols-[60px_1fr] min-h-[600px]">
                                <div>
                                    {Array.from({ length: 24 }, (_, h) => (
                                        <div key={h} className="h-12 border-b border-gray-50 px-2 flex items-start pt-0.5">
                                            <span className="text-[10px] text-gray-400">{String(h).padStart(2, '0')}:00</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="relative border-l border-gray-100">
                                    {Array.from({ length: 24 }, (_, h) => (
                                        <div key={h} className="h-12 border-b border-gray-50" />
                                    ))}
                                    {tasksOnDate(current).map((task, tIdx) => (
                                        <div
                                            key={task.id}
                                            onClick={() => setEditTask(task)}
                                            className="absolute left-1 right-1 px-2 py-1.5 rounded text-xs cursor-pointer hover:opacity-80 transition z-10"
                                            style={{ top: (9 + tIdx) * 48, backgroundColor: task.project_color + '20', color: task.project_color }}
                                        >
                                            <span className="font-medium">{task.title}</span>
                                            <span className="text-[10px] opacity-60 ml-1">{task.project_name}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Agenda View */}
                {view === 'agenda' && (
                    <div className="flex-1 overflow-y-auto">
                        {agendaWeeks.map((week) => (
                            <div key={week.label} className="border-b border-gray-100">
                                <div className="px-4 py-2 bg-gray-50 sticky top-0">
                                    <span className="text-xs font-semibold text-gray-600">{week.label}</span>
                                </div>
                                {week.days.filter((d) => d.tasks.length > 0).map((day) => (
                                    <div key={day.date} className="px-4 py-2 border-b border-gray-50 last:border-0">
                                        <div className="flex items-start gap-3">
                                            <div className="w-10 text-center flex-shrink-0">
                                                <div className="text-[10px] text-gray-400 uppercase">{day.short}</div>
                                                <div className={`text-sm font-bold ${day.isToday ? 'text-brand-500' : 'text-gray-800'}`}>{day.num}</div>
                                            </div>
                                            <div className="flex-1 space-y-1">
                                                {day.tasks.map((task) => (
                                                    <div
                                                        key={task.id}
                                                        onClick={() => setEditTask(task)}
                                                        className="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition"
                                                    >
                                                        <div className="w-2 h-2 rounded-full flex-shrink-0" style={{ backgroundColor: task.project_color }} />
                                                        <span className={`text-xs ${task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-700'}`}>{task.title}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ))}
                        {!agendaHasTasks && (
                            <div className="p-8 text-center text-gray-400 text-sm">No tasks in the next 3 weeks</div>
                        )}
                    </div>
                )}

                {/* Multi-Day View */}
                {view === 'multi-day' && (
                    <div className="flex-1 flex flex-col min-h-0">
                        <div className="grid border-b border-gray-100" style={{ gridTemplateColumns: 'repeat(7, 1fr)' }}>
                            {multiDays.map((day) => (
                                <div key={day.date} className="text-center py-2 border-r border-gray-100 last:border-r-0">
                                    <div className="text-[10px] font-semibold text-gray-500 uppercase">{day.short}</div>
                                    <div className={`text-lg font-bold mt-0.5 ${day.isToday ? 'text-brand-500' : 'text-gray-800'}`}>{day.num}</div>
                                </div>
                            ))}
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            <div className="grid min-h-[500px]" style={{ gridTemplateColumns: 'repeat(7, 1fr)' }}>
                                {multiDays.map((day) => (
                                    <div key={day.date} className="border-r border-gray-100 last:border-r-0 p-1.5 space-y-1">
                                        {tasksOnDate(day.date).map((task) => (
                                            <div
                                                key={task.id}
                                                onClick={() => setEditTask(task)}
                                                className="px-2 py-1.5 rounded text-[11px] cursor-pointer hover:opacity-80 transition"
                                                style={{ backgroundColor: task.project_color + '15', color: task.project_color }}
                                            >
                                                <span className="font-medium">{task.title}</span>
                                            </div>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Multi-Week View */}
                {view === 'multi-week' && (
                    <div className="flex-1 flex flex-col min-h-0">
                        <div className="grid grid-cols-7 border-b border-gray-100">
                            {WEEKDAYS_SHORT.map((d) => (
                                <div key={d} className="text-xs font-semibold text-gray-500 py-2 text-center">{d}</div>
                            ))}
                        </div>
                        <div className="grid grid-cols-7 flex-1 min-h-0" style={{ gridTemplateRows: 'repeat(3, 1fr)' }}>
                            {multiWeekCells.map((cell, idx) => {
                                const cellTasks = tasksOnDate(cell.date);
                                return (
                                    <div
                                        key={idx}
                                        onClick={() => goToDate(cell.date)}
                                        className={`border-r border-b border-gray-100 p-1 overflow-hidden cursor-pointer hover:bg-gray-50/50 transition ${!cell.currentMonth ? 'bg-gray-50/30' : ''}`}
                                    >
                                        <span className={`text-[10px] w-5 h-5 flex items-center justify-center rounded-full mb-0.5 ${
                                            cell.isToday ? 'bg-brand-500 text-white font-semibold'
                                                : cell.currentMonth ? 'text-gray-700' : 'text-gray-300'
                                        }`}>{cell.day}</span>
                                        <div className="space-y-px">
                                            {cellTasks.slice(0, 2).map((task) => (
                                                <div
                                                    key={task.id}
                                                    onClick={(e) => { e.stopPropagation(); setEditTask(task); }}
                                                    className="text-[9px] px-1 py-0.5 rounded truncate cursor-pointer hover:opacity-80 transition"
                                                    style={{ backgroundColor: task.project_color + '20', color: task.project_color }}
                                                >
                                                    {task.title}
                                                </div>
                                            ))}
                                            {cellTasks.length > 2 && (
                                                <div className="text-[8px] text-gray-400 px-1">+{cellTasks.length - 2}</div>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            {/* Edit Panel */}
            {editTask && (
                <>
                    <div className="fixed inset-0 bg-black/30 z-40" onClick={() => setEditTask(null)} />
                    <div className="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">{editTask.title}</h2>
                            <button onClick={() => setEditTask(null)} className="text-gray-400 hover:text-gray-600 transition">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div className="p-6 space-y-4 flex-1 overflow-y-auto">
                            <div>
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Status</label>
                                <span className={`text-sm px-2 py-1 rounded-full ${editTask.status === 'done' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}`}>
                                    {editTask.status === 'done' ? 'Completed' : 'Pending'}
                                </span>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Priority</label>
                                <div className="flex items-center gap-1.5">
                                    <svg className={`w-4 h-4 ${editTask.priority === 'high' ? 'text-red-500' : editTask.priority === 'medium' ? 'text-yellow-500' : 'text-green-500'}`} viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                                        <line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" strokeWidth="2" />
                                    </svg>
                                    <span className="text-sm capitalize text-gray-700">{editTask.priority}</span>
                                </div>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Due Date</label>
                                <span className="text-sm text-gray-700">{editTask.due_date}</span>
                            </div>
                            {editTask.project_name && (
                                <div>
                                    <label className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Project</label>
                                    <div className="flex items-center gap-2">
                                        <div className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: editTask.project_color }} />
                                        <span className="text-sm text-gray-700">{editTask.project_name}</span>
                                    </div>
                                </div>
                            )}
                            <div className="pt-2 border-t border-gray-100">
                                <Link href={`/dashboard?edit=${editTask.id}`} className="text-sm text-brand-500 hover:text-brand-600 font-medium transition">
                                    Open full editor
                                </Link>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
