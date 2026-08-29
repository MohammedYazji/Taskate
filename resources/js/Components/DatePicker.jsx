import { useState, useEffect, useRef } from 'react';

const TIME_OPTIONS = Array.from({ length: 48 }, (_, i) => {
    const h = String(Math.floor(i / 2)).padStart(2, '0');
    const m = i % 2 === 0 ? '00' : '30';
    return `${h}:${m}`;
});

const REMINDERS = [
    { value: 'on-time', label: 'On time' },
    { value: '5m', label: '5 min before' },
    { value: '30m', label: '30 min before' },
    { value: '1h', label: '1 hour before' },
    { value: '1d', label: '1 day before' },
];

const REPEATS = [
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
    { value: 'every-week', label: 'Every Week' },
];

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function SunIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="12" r="5" />
            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
        </svg>
    );
}

function SunriseIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M17 18a5 5 0 00-10 0" />
            <path d="M12 2v7M4.22 10.22l1.42 1.42M1 18h2M21 18h2M18.36 11.64l1.42-1.42" />
            <path d="M23 22H1" />
        </svg>
    );
}

function CalendarPlusIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
            <path d="M12 14v4M10 16h4" />
        </svg>
    );
}

function MoonIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
        </svg>
    );
}

function ClockIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 6v6l4 2" />
        </svg>
    );
}

function BellIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 01-3.46 0" />
        </svg>
    );
}

function RepeatIcon({ className = 'w-4 h-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M17 1l4 4-4 4" />
            <path d="M3 11V9a4 4 0 014-4h14" />
            <path d="M7 23l-4-4 4-4" />
            <path d="M21 13v2a4 4 0 01-4 4H3" />
        </svg>
    );
}

function ChevronIcon({ className = 'w-3.5 h-3.5', direction = 'down' }) {
    const rotation = { down: 0, up: 180, left: 90, right: -90 };
    return (
        <svg className={className} style={{ transform: `rotate(${rotation[direction]}deg)` }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M6 9l6 6 6-6" />
        </svg>
    );
}

export default function DatePicker({ value, onChange, iconMode = false, label = 'Due Date', repeatValue, onRepeat }) {
    const [open, setOpen] = useState(false);
    const [tab, setTab] = useState('date');
    const [selectedDate, setSelectedDate] = useState(value || null);
    const [selectedTime, setSelectedTime] = useState(null);
    const [reminder, setReminder] = useState('none');
    const [repeat, setRepeat] = useState(repeatValue || 'none');
    const [calYear, setCalYear] = useState(new Date().getFullYear());
    const [calMonth, setCalMonth] = useState(new Date().getMonth());
    const [timeDropdownOpen, setTimeDropdownOpen] = useState(false);
    const [reminderDropdownOpen, setReminderDropdownOpen] = useState(false);
    const [repeatDropdownOpen, setRepeatDropdownOpen] = useState(false);
    const [monthPickerOpen, setMonthPickerOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        setSelectedDate(value || null);
    }, [value]);

    useEffect(() => {
        setRepeat(repeatValue || 'none');
    }, [repeatValue]);

    useEffect(() => {
        if (!open) return;
        const handleClick = (e) => {
            if (ref.current && !ref.current.contains(e.target)) {
                setOpen(false);
                setTimeDropdownOpen(false);
                setReminderDropdownOpen(false);
                setRepeatDropdownOpen(false);
                setMonthPickerOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, [open]);

    const today = new Date();
    const calDaysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const calStartDow = new Date(calYear, calMonth, 1).getMonth();
    const calMonthLabel = new Date(calYear, calMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
    const calDays = [];
    for (let i = 0; i < (calStartDow === 0 ? 6 : calStartDow - 1); i++) calDays.push(null);
    for (let d = 1; d <= calDaysInMonth; d++) calDays.push(d);

    const prevMonth = () => {
        if (calMonth === 0) { setCalMonth(11); setCalYear(y => y - 1); }
        else setCalMonth(m => m - 1);
    };

    const nextMonth = () => {
        if (calMonth === 11) { setCalMonth(0); setCalYear(y => y + 1); }
        else setCalMonth(m => m + 1);
    };

    const dateStr = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

    const isToday = (d) => d === today.getDate() && calMonth === today.getMonth() && calYear === today.getFullYear();

    const getRecurringDays = () => {
        if (!selectedDate || repeat === 'none') return [];
        const start = new Date(selectedDate + 'T00:00:00');
        const days = [];
        const current = new Date(calYear, calMonth, 1);
        const lastDay = new Date(calYear, calMonth + 1, 0).getDate();
        for (let d = 1; d <= lastDay; d++) {
            current.setDate(d);
            if (current < start) continue;
            const diffTime = current.getTime() - start.getTime();
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
            let match = false;
            if (repeat === 'daily') {
                match = diffDays >= 0;
            } else if (repeat === 'weekly' || repeat === 'every-week') {
                match = diffDays >= 0 && diffDays % 7 === 0;
            } else if (repeat === 'monthly') {
                match = current.getDate() === start.getDate() || (current.getDate() === lastDay && start.getDate() > lastDay);
            } else if (repeat === 'yearly') {
                match = current.getMonth() === start.getMonth() && current.getDate() === start.getDate();
            }
            if (match) days.push(d);
        }
        return days;
    };

    const recurringDays = getRecurringDays();

    const isRecurringDay = (d) => d && recurringDays.includes(d) && !isSelected(d);

    const isSelected = (d) => {
        if (!selectedDate || !d) return false;
        return selectedDate === dateStr(calYear, calMonth, d);
    };

    const pickDay = (d) => {
        if (!d) return;
        const ds = dateStr(calYear, calMonth, d);
        setSelectedDate(ds);
        onChange(ds);
    };

    const setQuickDate = (daysOffset) => {
        const d = new Date();
        d.setDate(d.getDate() + daysOffset);
        setCalYear(d.getFullYear());
        setCalMonth(d.getMonth());
        const ds = dateStr(d.getFullYear(), d.getMonth(), d.getDate());
        setSelectedDate(ds);
        onChange(ds);
    };

    const handleOk = () => {
        setOpen(false);
    };

    const handleClear = () => {
        setSelectedDate(null);
        setSelectedTime(null);
        setReminder('none');
        setRepeat('none');
        onChange(null);
        setOpen(false);
    };

    const formattedDisplay = selectedDate
        ? new Date(selectedDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) +
          (selectedTime ? ' ' + selectedTime : '')
        : '';

    const quickPicks = [
        { label: 'Today', offset: 0, Icon: SunIcon },
        { label: 'Tomorrow', offset: 1, Icon: SunriseIcon },
        { label: '+7 days', offset: 7, Icon: CalendarPlusIcon },
        { label: '+1 month', offset: 30, Icon: MoonIcon },
    ];

    const reminderLabel = REMINDERS.find(r => r.value === reminder)?.label || 'None';
    const repeatLabel = REPEATS.find(r => r.value === repeat)?.label || 'None';

    return (
        <div ref={ref} className="relative inline-block">
            <button onClick={() => setOpen(!open)}
                className={iconMode
                    ? "flex items-center gap-1.5 p-1.5 text-textMuted hover:text-ink rounded-lg transition"
                    : "w-full text-center text-xs font-medium border border-stone rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-brand-500 text-textMain hover:border-brand-400 transition"
                }>
                {iconMode && (
                    <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                )}
                <span className="text-xs text-textMuted">{formattedDisplay || label}</span>
            </button>

            {open && (
                <div className="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-72 bg-paper border border-stone rounded-2xl shadow-floating z-50 pt-3">
                    {/* Tabs */}
                    <div className="px-4 pt-1">
                        <div className="flex bg-stone/30 rounded-lg p-1">
                            <button onClick={() => setTab('date')}
                                className={`flex-1 py-2 text-xs font-semibold rounded-md transition ${tab === 'date' ? 'bg-paper text-brand-600 shadow-sm' : 'text-textMuted hover:text-textMain'}`}>
                                Date
                            </button>
                            <button onClick={() => setTab('duration')}
                                className={`flex-1 py-2 text-xs font-semibold rounded-md transition ${tab === 'duration' ? 'bg-paper text-brand-600 shadow-sm' : 'text-textMuted hover:text-textMain'}`}>
                                Duration
                            </button>
                        </div>
                    </div>

                    {tab === 'date' && (
                        <div className="p-4 pt-3 space-y-4">
                            {/* Quick picks */}
                            <div className="flex items-center justify-between px-1">
                                {quickPicks.map(q => (
                                    <div key={q.label} className="relative group/tip">
                                        <button onClick={() => setQuickDate(q.offset)}
                                            className="p-2.5 rounded-xl hover:bg-brand-50 transition text-textMuted hover:text-brand-500">
                                            <q.Icon className="w-5 h-5" />
                                        </button>
                                        <span className="pointer-events-none absolute -bottom-7 left-1/2 -translate-x-1/2 px-2 py-0.5 text-[10px] font-medium text-paper bg-ink rounded-md opacity-0 group-hover/tip:opacity-100 transition whitespace-nowrap shadow-lg z-50">
                                            {q.label}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            {/* Calendar */}
                            <div className="border border-stone rounded-xl p-3">
                                <div className="flex items-center justify-between mb-3">
                                    <button onClick={prevMonth} className="p-1 text-textMuted hover:text-textMain rounded-lg transition hover:bg-stone/20">
                                        <ChevronIcon direction="left" />
                                    </button>
                                    <button onClick={() => setMonthPickerOpen(!monthPickerOpen)}
                                        className="text-xs font-semibold text-textMain hover:text-brand-500 transition px-2 py-1 rounded-lg hover:bg-stone/20">
                                        {calMonthLabel}
                                    </button>
                                    <button onClick={nextMonth} className="p-1 text-textMuted hover:text-textMain rounded-lg transition hover:bg-stone/20">
                                        <ChevronIcon direction="right" />
                                    </button>
                                </div>

                                {/* Month picker overlay */}
                                {monthPickerOpen && (
                                    <div className="p-2 bg-stone/20 rounded-lg">
                                        <div className="flex items-center justify-between mb-2">
                                            <button onClick={() => setCalYear(y => y - 1)} className="p-1 text-textMuted hover:text-textMain rounded transition">
                                                <ChevronIcon direction="left" />
                                            </button>
                                            <span className="text-xs font-bold text-textMain">{calYear}</span>
                                            <button onClick={() => setCalYear(y => y + 1)} className="p-1 text-textMuted hover:text-textMain rounded transition">
                                                <ChevronIcon direction="right" />
                                            </button>
                                        </div>
                                        <div className="grid grid-cols-4 gap-1.5">
                                            {MONTHS.map((m, i) => (
                                                <button key={m} onClick={() => { setCalMonth(i); setMonthPickerOpen(false); }}
                                                    className={`px-2 py-1.5 text-[10px] font-medium rounded-lg transition ${
                                                        i === calMonth ? 'bg-brand-500 text-paper' : 'text-textMain hover:bg-brand-50 hover:text-brand-600'
                                                    }`}>
                                                    {m}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {!monthPickerOpen && (
                                    <div className="grid grid-cols-7 gap-0.5 text-center">
                                        {['S', 'M', 'T', 'W', 'T', 'F', 'S'].map((d, i) => (
                                            <div key={i} className="text-[10px] font-semibold text-textMuted py-1">{d}</div>
                                        ))}
                                        {calDays.map((day, idx) => (
                                            <button key={idx} onClick={() => pickDay(day)}
                                                className={`w-8 h-8 mx-auto text-xs rounded-full flex items-center justify-center transition relative ${
                                                    day && isSelected(day) ? 'bg-brand-500 text-paper font-semibold shadow-sm' :
                                                    day && isToday(day) ? 'bg-brand-50 text-brand-600 font-semibold ring-1 ring-brand-200' :
                                                    day && isRecurringDay(day) ? 'text-ochre font-semibold' :
                                                    day ? 'text-textMain hover:bg-stone/20' : 'text-transparent cursor-default'
                                                }`}>
                                                {day || ''}
                                                {day && isRecurringDay(day) && (
                                                    <span className="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-ochre"></span>
                                                )}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Time dropdown */}
                            <div className="relative">
                                <button onClick={() => { setTimeDropdownOpen(!timeDropdownOpen); setReminderDropdownOpen(false); setRepeatDropdownOpen(false); }}
                                    className="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-medium border border-stone rounded-xl hover:border-brand-400 transition text-textMain">
                                    <ClockIcon className="w-4 h-4 text-textMuted flex-shrink-0" />
                                    <span className="flex-1 text-left">{selectedTime || 'Time'}</span>
                                    <ChevronIcon className="w-3.5 h-3.5 text-textMuted flex-shrink-0" direction={timeDropdownOpen ? 'up' : 'down'} />
                                </button>
                                {timeDropdownOpen && (
                                    <div className="absolute top-full left-0 right-0 mt-1 bg-paper border border-stone rounded-xl shadow-floating z-10 max-h-48 overflow-y-auto">
                                        <button onClick={() => { setSelectedTime(null); setTimeDropdownOpen(false); }}
                                            className="w-full px-3 py-2 text-xs text-left hover:bg-brand-50 transition text-textMuted border-b border-stone">
                                            No time
                                        </button>
                                        {TIME_OPTIONS.map(t => (
                                            <button key={t} onClick={() => { setSelectedTime(t); setTimeDropdownOpen(false); }}
                                                className={`w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition ${selectedTime === t ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-textMain'}`}>
                                                {t}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Reminder dropdown */}
                            <div className="relative">
                                <button onClick={() => { setReminderDropdownOpen(!reminderDropdownOpen); setTimeDropdownOpen(false); setRepeatDropdownOpen(false); }}
                                    className="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-medium border border-stone rounded-xl hover:border-brand-400 transition text-textMain">
                                    <BellIcon className="w-4 h-4 text-textMuted flex-shrink-0" />
                                    <span className="flex-1 text-left">
                                        {reminder !== 'none' ? `Reminder: ${reminderLabel}` : 'Reminder'}
                                    </span>
                                    <ChevronIcon className="w-3.5 h-3.5 text-textMuted flex-shrink-0" direction={reminderDropdownOpen ? 'up' : 'down'} />
                                </button>
                                {reminderDropdownOpen && (
                                    <div className="absolute bottom-full left-0 right-0 mb-1 bg-paper border border-stone rounded-xl shadow-floating z-10">
                                        <button onClick={() => { setReminder('none'); setReminderDropdownOpen(false); }}
                                            className="w-full px-3 py-2 text-xs text-left hover:bg-brand-50 transition text-textMuted border-b border-stone">
                                            None
                                        </button>
                                        {REMINDERS.map(r => (
                                            <button key={r.value} onClick={() => { setReminder(r.value); setReminderDropdownOpen(false); }}
                                                className={`w-full px-3 py-2 text-xs text-left hover:bg-brand-50 transition ${reminder === r.value ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-textMain'}`}>
                                                {r.label}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Repeat dropdown */}
                            <div className="relative">
                                <button onClick={() => { setRepeatDropdownOpen(!repeatDropdownOpen); setTimeDropdownOpen(false); setReminderDropdownOpen(false); }}
                                    className="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-medium border border-stone rounded-xl hover:border-brand-400 transition text-textMain">
                                    <RepeatIcon className="w-4 h-4 text-textMuted flex-shrink-0" />
                                    <span className="flex-1 text-left">
                                        {repeat !== 'none' ? `Repeat: ${repeatLabel}` : 'Repeat'}
                                    </span>
                                    <ChevronIcon className="w-3.5 h-3.5 text-textMuted flex-shrink-0" direction={repeatDropdownOpen ? 'up' : 'down'} />
                                </button>
                                {repeatDropdownOpen && (
                                    <div className="absolute bottom-full left-0 right-0 mb-1 bg-paper border border-stone rounded-xl shadow-floating z-10">
                                        <button onClick={() => { setRepeat('none'); setRepeatDropdownOpen(false); onRepeat?.(null); }}
                                            className="w-full px-3 py-2 text-xs text-left hover:bg-brand-50 transition text-textMuted border-b border-stone">
                                            None
                                        </button>
                                        {REPEATS.map(r => (
                                            <button key={r.value} onClick={() => { setRepeat(r.value); setRepeatDropdownOpen(false); onRepeat?.(r.value); }}
                                                className={`w-full px-3 py-2 text-xs text-left hover:bg-brand-50 transition ${repeat === r.value ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-textMain'}`}>
                                                {r.label}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {tab === 'duration' && (
                        <div className="p-4 pt-3 space-y-4">
                            <div className="space-y-1">
                                <label className="text-[11px] font-semibold text-textMuted uppercase tracking-wide">Start Date</label>
                                <input type="date" className="w-full text-xs font-medium border border-stone rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-textMain" />
                            </div>
                            <div className="space-y-1">
                                <label className="text-[11px] font-semibold text-textMuted uppercase tracking-wide">End Date</label>
                                <input type="date" className="w-full text-xs font-medium border border-stone rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-textMain" />
                            </div>
                        </div>
                    )}

                    {/* Footer */}
                    <div className="flex items-center justify-end gap-2 px-4 py-3 border-t border-stone">
                        <button onClick={handleClear}
                            className="px-3 py-1.5 text-xs font-medium text-textMuted hover:text-textMain rounded-lg hover:bg-stone/20 transition">
                            Clear
                        </button>
                        <button onClick={handleOk}
                            className="px-4 py-1.5 text-xs font-medium text-paper bg-brand-500 hover:bg-brand-600 rounded-lg transition shadow-sm">
                            OK
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
