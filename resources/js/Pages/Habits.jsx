import { useState, useCallback, useRef } from "react";
import { HabitsSkeleton } from "@/Components/Skeleton";
import { useLoading } from "@/Components/LoadingContext";

const ICONS = [
    "🎯","💪","🏃","📚","💧","🧘","😴","🥗","💊","🎨",
    "✍️","🎵","🧹","🛒","💰","🌱","🐕","👶","❤️","🙏",
    "☕","🍎","🩸","🧠","⏰","📞","💄","💇","🏋️","🚴",
    "🏊","📖","💤","🚭",
];

const DEFAULT_SECTIONS = ["Morning", "Afternoon", "Night", "Others"];

const GOAL_DAY_OPTIONS = [
    { value: 0, label: "Forever" },
    { value: 7, label: "7 days" },
    { value: 21, label: "21 days" },
    { value: 30, label: "30 days" },
    { value: 100, label: "100 days" },
    { value: 365, label: "365 days" },
];

const DAY_NAMES_SHORT = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
const DAY_NAMES = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
];
const MONTH_NAMES = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December",
];

function fmt(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
}

function addDays(d, n) {
    const r = new Date(d);
    r.setDate(r.getDate() + n);
    return r;
}

function startOfWeek(d) {
    const r = new Date(d);
    const day = r.getDay();
    const diff = (day === 0 ? -6 : 1) - day;
    r.setDate(r.getDate() + diff);
    r.setHours(0, 0, 0, 0);
    return r;
}

function daysInMonth(y, m) {
    return new Date(y, m + 1, 0).getDate();
}

function isInHabitRange(habit, dateStr) {
    if (!habit || !habit.start_date) return true;
    const d = new Date(dateStr + "T00:00:00");
    const start = new Date(habit.start_date + "T00:00:00");
    if (d < start) return false;
    if (habit.goal_days && habit.goal_days > 0) {
        const end = new Date(start);
        end.setDate(end.getDate() + habit.goal_days - 1);
        if (d > end) return false;
    }
    if (
        habit.frequency_type === "daily" &&
        habit.frequency_config?.days?.length > 0
    ) {
        const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        if (!habit.frequency_config.days.includes(dayNames[d.getDay()]))
            return false;
    }
    if (habit.frequency_type === "interval" && habit.frequency_config) {
        const intervalDays =
            habit.frequency_config.interval || habit.frequency_config.every || 2;
        const diff = Math.floor((d - start) / (1000 * 60 * 60 * 24));
        if (diff % intervalDays !== 0) return false;
    }
    return true;
}

function api(url, method, body) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const opts = {
        method,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf,
            "X-Requested-With": "XMLHttpRequest",
        },
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts).then((r) => r.json());
}

function ProgressRing({ progress, size = 40, stroke = 3, color, count, goalType, isCompleted }) {
    const r = (size - stroke) / 2;
    const circ = 2 * Math.PI * r;
    const offset = circ - circ * Math.min(progress, 1);
    const ringColor = isCompleted ? "#f59e0b" : color || "#14B8A6";

    return (
        <div className="relative" style={{ width: size, height: size }}>
            <svg
                className="-rotate-90"
                width={size}
                height={size}
                viewBox={`0 0 ${size} ${size}`}
            >
                <circle
                    cx={size / 2}
                    cy={size / 2}
                    r={r}
                    fill="none"
                    stroke="#e5e7eb"
                    strokeWidth={stroke}
                />
                <circle
                    cx={size / 2}
                    cy={size / 2}
                    r={r}
                    fill="none"
                    stroke={ringColor}
                    strokeWidth={stroke}
                    strokeLinecap="round"
                    strokeDasharray={circ}
                    strokeDashoffset={offset}
                    className="transition-all duration-300"
                />
            </svg>
            <div className="absolute inset-0 flex items-center justify-center">
                {goalType === "count" ? (
                    <span className="text-[10px] font-bold text-gray-700">{count}</span>
                ) : isCompleted ? (
                    <svg className="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                    </svg>
                ) : null}
            </div>
        </div>
    );
}

function IconPicker({ value, onChange, onClose }) {
    return (
        <>
            <div className="fixed inset-0 z-40" onClick={onClose} />
            <div className="absolute top-full left-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-200 p-3 grid grid-cols-8 gap-1 z-50 w-72">
                {ICONS.map((ic) => (
                    <button
                        key={ic}
                        onClick={() => { onChange(ic); onClose(); }}
                        className="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-lg transition"
                    >
                        {ic}
                    </button>
                ))}
            </div>
        </>
    );
}

export default function Habits({ habits: initialHabits }) {
    const { loading } = useLoading();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const todayStr = fmt(today);

    const [habits, setHabits] = useState(initialHabits);
    const [selectedDate, setSelectedDate] = useState(todayStr);
    const [weekOffset, setWeekOffset] = useState(0);

    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState("create");
    const [form, setForm] = useState(null);

    const [detailOpen, setDetailOpen] = useState(false);
    const [detailHabit, setDetailHabit] = useState(null);
    const [detailStats, setDetailStats] = useState(null);
    const [detailMonth, setDetailMonth] = useState(today.getMonth());
    const [detailYear, setDetailYear] = useState(today.getFullYear());
    const [chartPage, setChartPage] = useState(0);
    const [todayNote, setTodayNote] = useState("");
    const [showArchived, setShowArchived] = useState(false);

    const [iconPickerOpen, setIconPickerOpen] = useState(false);
    const [addingSection, setAddingSection] = useState(false);
    const [newSectionName, setNewSectionName] = useState("");
    const [customSections, setCustomSections] = useState([]);
    const [startCalOpen, setStartCalOpen] = useState(false);
    const [startCalMonth, setStartCalMonth] = useState(today.getMonth());
    const [startCalYear, setStartCalYear] = useState(today.getFullYear());

    const chartWindowDays = 14;

    const weekDays = () => {
        const start = addDays(startOfWeek(today), weekOffset * 7);
        const days = [];
        for (let i = 0; i < 7; i++) {
            const d = addDays(start, i);
            if (d > today) break;
            days.push({
                date: fmt(d),
                short: DAY_NAMES[d.getDay()].slice(0, 3),
                num: d.getDate(),
                isToday: fmt(d) === todayStr,
            });
        }
        return days;
    };

    const weekLabel = () => {
        const days = weekDays();
        if (days.length === 0) return "";
        return `${days[0].short} ${days[0].num} – ${days[days.length - 1].short} ${days[days.length - 1].num}, ${days[days.length - 1].date.slice(0, 4)}`;
    };

    const activeHabits = habits.filter((h) => !h.is_archived);
    const archivedHabits = habits.filter((h) => h.is_archived);

    const isCompleted = (habit, date) =>
        habit?.checkins?.some((c) => c.date === date && c.completed) || false;

    const todayCount = (habit) => {
        const ck = habit?.checkins?.find((c) => c.date === selectedDate);
        return ck ? ck.count || 0 : 0;
    };

    const habitProgress = (habit) => {
        if (!habit) return 0;
        if (habit.goal_type === "boolean")
            return isCompleted(habit, selectedDate) ? 1 : 0;
        const target = habit.goal_config?.target || 1;
        return Math.min(todayCount(habit) / target, 1);
    };

    const completedToday = activeHabits.filter(
        (h) => isCompleted(h, selectedDate) && isInHabitRange(h, selectedDate),
    ).length;

    const bestStreak =
        activeHabits.length > 0
            ? Math.max(...activeHabits.map((h) => h.streak || 0))
            : 0;

    const completionRate =
        activeHabits.length > 0
            ? Math.round(
                  (activeHabits.filter((h) => isCompleted(h, todayStr)).length /
                      activeHabits.length) *
                      100,
              )
            : 0;

    const groupedHabits = () => {
        const active = activeHabits.filter((h) =>
            isInHabitRange(h, selectedDate),
        );
        const groups = {};
        active.forEach((h) => {
            const sec = h.section || "Others";
            if (!groups[sec]) groups[sec] = [];
            groups[sec].push(h);
        });
        return Object.keys(groups).map((name) => ({
            name,
            habits: groups[name],
        }));
    };

    const allSections = () => {
        const existing = [
            ...new Set(habits.map((h) => h.section).filter(Boolean)),
        ].filter((s) => !DEFAULT_SECTIONS.includes(s));
        return [
            ...DEFAULT_SECTIONS,
            ...customSections.filter(
                (s) =>
                    !DEFAULT_SECTIONS.includes(s) && !existing.includes(s),
            ),
        ];
    };

    const toggleHabit = (habit, date) => {
        if (!habit || !isInHabitRange(habit, date)) return;
        api(`/habits/${habit.id}/toggle`, "PATCH", { date }).then((data) => {
            setHabits((prev) =>
                prev.map((h) => {
                    if (h.id !== habit.id) return h;
                    let checkins = [...(h.checkins || [])];
                    const idx = checkins.findIndex((c) => c.date === date);
                    if (data.completed === false && h.goal_type !== "count") {
                        checkins = checkins.filter((c) => c.date !== date);
                    } else if (idx >= 0) {
                        checkins[idx] = {
                            date,
                            completed: data.completed,
                            count: data.count || 0,
                            note: checkins[idx].note || "",
                        };
                    } else {
                        checkins.push({
                            date,
                            completed: data.completed,
                            count: data.count || 0,
                        });
                    }
                    return { ...h, checkins };
                }),
            );
            if (detailHabit?.id === habit.id) {
                setDetailHabit((prev) => {
                    if (!prev) return prev;
                    let checkins = [...(prev.checkins || [])];
                    const idx = checkins.findIndex((c) => c.date === date);
                    if (data.completed === false && prev.goal_type !== "count") {
                        checkins = checkins.filter((c) => c.date !== date);
                    } else if (idx >= 0) {
                        checkins[idx] = {
                            date,
                            completed: data.completed,
                            count: data.count || 0,
                            note: checkins[idx].note || "",
                        };
                    } else {
                        checkins.push({
                            date,
                            completed: data.completed,
                            count: data.count || 0,
                        });
                    }
                    return { ...prev, checkins };
                });
            }
        });
    };

    const openCreate = () => {
        setModalMode("create");
        setForm({
            id: null,
            name: "",
            icon: "🎯",
            frequency_type: "daily",
            frequency_config: {},
            goal_type: "boolean",
            goal_config: { target: 1, unit: "Count", increment: 1 },
            start_date: todayStr,
            section: "Others",
            goal_days: 0,
            reminders: [],
            auto_popup: false,
        });
        setStartCalMonth(today.getMonth());
        setStartCalYear(today.getFullYear());
        setModalOpen(true);
    };

    const openEdit = (habit) => {
        setModalMode("edit");
        setForm({
            ...JSON.parse(JSON.stringify(habit)),
            frequency_config: habit.frequency_config || {},
            goal_config: habit.goal_config || { target: 1, unit: "Count", increment: 1 },
            reminders: habit.reminders || [],
        });
        if (habit.start_date) {
            const sd = new Date(habit.start_date + "T00:00:00");
            setStartCalMonth(sd.getMonth());
            setStartCalYear(sd.getFullYear());
        }
        setDetailOpen(false);
        setModalOpen(true);
    };

    const saveHabit = () => {
        if (!form?.name?.trim()) return;
        const url =
            modalMode === "create" ? "/habits" : `/habits/${form.id}`;
        const method = modalMode === "create" ? "POST" : "PATCH";
        api(url, method, form).then((data) => {
            if (modalMode === "create") {
                data.checkins = [];
                setHabits((prev) => [...prev, data]);
            } else {
                setHabits((prev) =>
                    prev.map((h) =>
                        h.id === data.id
                            ? { ...data, checkins: h.checkins }
                            : h,
                    ),
                );
            }
            setModalOpen(false);
        });
    };

    const deleteHabit = () => {
        if (!confirm("Delete this habit? This cannot be undone.")) return;
        api(`/habits/${form.id}`, "DELETE").then(() => {
            setHabits((prev) => prev.filter((h) => h.id !== form.id));
            setModalOpen(false);
            setDetailOpen(false);
        });
    };

    const archiveHabit = (habit) => {
        api(`/habits/${habit.id}/archive`, "PATCH").then((data) => {
            setHabits((prev) =>
                prev.map((h) =>
                    h.id === habit.id ? { ...h, is_archived: data.archived } : h,
                ),
            );
        });
    };

    const openDetail = (habit) => {
        setDetailHabit(habit);
        setDetailMonth(today.getMonth());
        setDetailYear(today.getFullYear());
        setChartPage(0);
        setDetailOpen(true);
        loadDetailStats(habit, today.getMonth(), today.getFullYear());
    };

    const loadDetailStats = (habit, month, year) => {
        api(
            `/habits/${habit.id}/stats?year=${year}&month=${month + 1}`,
            "GET",
        ).then((data) => {
            setDetailStats(data);
            setChartPage(0);
        });
    };

    const saveTodayNote = () => {
        if (!detailHabit) return;
        api(`/habits/${detailHabit.id}/toggle`, "PATCH", {
            date: selectedDate,
            note: todayNote,
        }).then((data) => {
            setDetailHabit((prev) => {
                if (!prev) return prev;
                let checkins = [...(prev.checkins || [])];
                const idx = checkins.findIndex((c) => c.date === selectedDate);
                const newCheckin = {
                    date: selectedDate,
                    completed: idx >= 0 ? checkins[idx].completed : data.completed,
                    count: idx >= 0 ? checkins[idx].count : data.count,
                    note: data.note,
                };
                if (idx >= 0) checkins[idx] = newCheckin;
                else checkins.push(newCheckin);
                return { ...prev, checkins };
            });
        });
    };

    const loadSelectedDateNote = () => {
        if (!detailHabit) return;
        const ck = detailHabit.checkins?.find((c) => c.date === selectedDate);
        const statsCk = detailStats?.monthly_checkins_data?.find(
            (c) => c.date === selectedDate,
        );
        const note = ck?.note || statsCk?.note || "";
        setTodayNote(note);
    };

    const detailMonthLabel = () => `${MONTH_NAMES[detailMonth]} ${detailYear}`;

    const detailCalendarBlanks = () => {
        const first = new Date(detailYear, detailMonth, 1);
        const day = first.getDay();
        return day === 0 ? 6 : day - 1;
    };

    const detailCalendarDays = () => {
        const total = daysInMonth(detailYear, detailMonth);
        const days = [];
        for (let i = 1; i <= total; i++) {
            const d = new Date(detailYear, detailMonth, i);
            days.push({ date: fmt(d), day: i, isToday: fmt(d) === todayStr });
        }
        return days;
    };

    const startCalBlanks = () => {
        const first = new Date(startCalYear, startCalMonth, 1);
        const day = first.getDay();
        return day === 0 ? 6 : day - 1;
    };

    const startCalDays = () => {
        const total = daysInMonth(startCalYear, startCalMonth);
        const days = [];
        for (let i = 1; i <= total; i++) {
            const d = new Date(startCalYear, startCalMonth, i);
            days.push({ date: fmt(d), day: i, isToday: fmt(d) === todayStr });
        }
        return days;
    };

    const chartFilteredData = () => {
        const data = detailStats?.monthly_checkins_data || [];
        const target = detailHabit?.goal_config?.target || 1;
        return data
            .filter((c) => c.count > 0)
            .map((c) => ({
                date: c.date,
                count: Math.min(c.count, target),
                day: parseInt(c.date.split("-")[2]),
            }))
            .sort((a, b) => a.date.localeCompare(b.date));
    };

    const chartAllDaysInMonth = () => {
        const total = daysInMonth(detailYear, detailMonth);
        const days = [];
        for (let i = 1; i <= total; i++) {
            const d = new Date(detailYear, detailMonth, i);
            days.push({ date: fmt(d), day: i });
        }
        return days;
    };

    const chartPageDays = () => {
        const all = chartAllDaysInMonth();
        const start = chartPage * chartWindowDays;
        return all.slice(start, start + chartWindowDays);
    };

    const chartMaxPage = () => {
        const total = daysInMonth(detailYear, detailMonth);
        return Math.max(0, Math.ceil(total / chartWindowDays) - 1);
    };

    const chartSvgWidth = () => chartPageDays().length * 32 + 24;

    const chartYTicks = () => {
        const target = detailHabit?.goal_config?.target || 1;
        const ticks = [];
        for (let i = 0; i <= target; i++) ticks.push(i);
        return ticks;
    };

    const chartY = (val) => {
        const target = detailHabit?.goal_config?.target || 1;
        const top = 16,
            bottom = 118;
        if (target === 0) return bottom;
        return bottom - (val / target) * (bottom - top);
    };

    const chartPoints = () => {
        const pageDays = chartPageDays();
        const data = chartFilteredData();
        const dataMap = {};
        data.forEach((d) => (dataMap[d.date] = d.count));
        return pageDays
            .map((d, i) => {
                const val = dataMap[d.date];
                if (val !== undefined)
                    return {
                        date: d.date,
                        dayNum: d.day,
                        x: i * 32 + 24,
                        y: chartY(val),
                        val,
                    };
                return null;
            })
            .filter(Boolean);
    };

    const chartLinePath = () => {
        const pts = chartPoints();
        if (pts.length < 2) return "";
        const segs = [];
        let i = 0;
        while (i < pts.length) {
            const seg = [pts[i]];
            let j = i + 1;
            while (j < pts.length) {
                const prevDay = parseInt(pts[j - 1].date.split("-")[2]);
                const curDay = parseInt(pts[j].date.split("-")[2]);
                if (curDay - prevDay === 1) {
                    seg.push(pts[j]);
                    j++;
                } else break;
            }
            if (seg.length >= 2) {
                let d = `M${seg[0].x},${seg[0].y}`;
                for (let k = 1; k < seg.length; k++)
                    d += `L${seg[k].x},${seg[k].y}`;
                segs.push(d);
            }
            i = j;
        }
        return segs.join(" ");
    };

    const chartScrollWidth = () => {
        const total = daysInMonth(detailYear, detailMonth);
        return Math.min(100, (chartWindowDays / total) * 100);
    };

    const chartScrollOffset = () => {
        const total = daysInMonth(detailYear, detailMonth);
        return ((chartPage * chartWindowDays) / total) * 100;
    };

    const updateForm = (field, value) => setForm((prev) => ({ ...prev, [field]: value }));

    const updateFreqConfig = (field, value) =>
        setForm((prev) => ({
            ...prev,
            frequency_config: { ...prev.frequency_config, [field]: value },
        }));

    const updateGoalConfig = (field, value) =>
        setForm((prev) => ({
            ...prev,
            goal_config: { ...prev.goal_config, [field]: value },
        }));

    const toggleWeekDay = (dayName) => {
        const days = form.frequency_config?.days || [];
        const idx = days.indexOf(dayName);
        updateFreqConfig(
            "days",
            idx >= 0 ? days.filter((d) => d !== dayName) : [...days, dayName],
        );
    };

    const formatStartDate = () => {
        if (!form?.start_date) return "Today";
        const d = new Date(form.start_date + "T00:00:00");
        return `${DAY_NAMES[d.getDay()]}, ${MONTH_NAMES[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
    };

    const formatLogDate = (dateStr) => {
        const d = new Date(dateStr + "T00:00:00");
        return `${DAY_NAMES[d.getDay()]}, ${MONTH_NAMES[d.getMonth()]} ${d.getDate()}`;
    };

    if (loading) return <HabitsSkeleton />;

    return (
        <div className="max-w-5xl mx-auto select-none">
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Habits</h1>
                    <p className="text-sm text-gray-500 mt-0.5">
                        {activeHabits.length} active · {archivedHabits.length} archived
                    </p>
                </div>
                <button
                    onClick={openCreate}
                    className="flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition shadow-sm"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Habit
                </button>
            </div>

            {/* Week Strip */}
            <div className="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
                <div className="flex items-center justify-between mb-3">
                    <button
                        onClick={() => setWeekOffset((p) => p - 1)}
                        className="p-1.5 hover:bg-gray-100 rounded-lg transition"
                    >
                        <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span className="text-sm font-medium text-gray-700">{weekLabel()}</span>
                    <button
                        onClick={() => setWeekOffset((p) => p + 1)}
                        className="p-1.5 hover:bg-gray-100 rounded-lg transition"
                    >
                        <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <div className="grid grid-cols-7 gap-2">
                    {weekDays().map((day) => (
                        <button
                            key={day.date}
                            onClick={() => setSelectedDate(day.date)}
                            className={`flex flex-col items-center py-2.5 rounded-xl transition text-xs ${
                                selectedDate === day.date
                                    ? "bg-brand-500 text-white shadow-sm"
                                    : day.isToday
                                      ? "bg-brand-50 text-brand-600 ring-1 ring-brand-300"
                                      : "text-gray-600 hover:bg-gray-50"
                            }`}
                        >
                            <span className="font-medium">{day.short}</span>
                            <span className="text-lg font-bold mt-0.5">{day.num}</span>
                        </button>
                    ))}
                </div>
            </div>

            {/* Stats Summary */}
            <div className="grid grid-cols-3 gap-4 mb-6">
                <div className="bg-white rounded-2xl border border-gray-200 p-4 text-center shadow-sm">
                    <div className="text-2xl font-bold text-brand-500">{completedToday}</div>
                    <div className="text-xs text-gray-500 mt-1">
                        {selectedDate === todayStr ? "Done today" : `Done on ${new Date(selectedDate + "T00:00:00").toLocaleDateString("en-US", { month: "short", day: "numeric" })}`}
                    </div>
                </div>
                <div className="bg-white rounded-2xl border border-gray-200 p-4 text-center shadow-sm">
                    <div className="text-2xl font-bold text-orange-500">{bestStreak}</div>
                    <div className="text-xs text-gray-500 mt-1">Best streak</div>
                </div>
                <div className="bg-white rounded-2xl border border-gray-200 p-4 text-center shadow-sm">
                    <div className="text-2xl font-bold text-blue-500">{completionRate}%</div>
                    <div className="text-xs text-gray-500 mt-1">Completion rate</div>
                </div>
            </div>

            {/* Habit Groups */}
            {groupedHabits().map((group) => (
                <div key={group.name} className="mb-6">
                    <div className="flex items-center gap-2 mb-3">
                        <h2 className="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            {group.name}
                        </h2>
                        <span className="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                            {group.habits.length}
                        </span>
                    </div>
                    <div className="space-y-2">
                        {group.habits.map((habit) => (
                            <div
                                key={habit.id}
                                className="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow cursor-pointer group"
                                onClick={() => openDetail(habit)}
                            >
                                <span className="text-2xl flex-shrink-0">{habit.icon}</span>
                                <div className="flex-1 min-w-0">
                                    <div className="font-medium text-gray-900 text-sm truncate">
                                        {habit.name}
                                    </div>
                                    <div className="flex items-center gap-2 mt-0.5">
                                        <span className="text-xs font-bold text-yellow-500">
                                            ⚡{habit.total_checkins || 0}
                                        </span>
                                        <span className="text-xs font-bold text-orange-500">
                                            🔥{habit.streak || 0}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    className="flex items-center gap-3 flex-shrink-0"
                                    onClick={(e) => e.stopPropagation()}
                                >
                                    <div
                                        className={!isInHabitRange(habit, selectedDate) ? "opacity-40 pointer-events-none" : ""}
                                        onClick={() => toggleHabit(habit, selectedDate)}
                                    >
                                        <ProgressRing
                                            progress={habitProgress(habit)}
                                            count={todayCount(habit)}
                                            goalType={habit.goal_type}
                                            isCompleted={isCompleted(habit, selectedDate)}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ))}

            {/* Empty state */}
            {habits.length === 0 && (
                <div className="text-center py-16">
                    <div className="text-6xl mb-4">🎯</div>
                    <h3 className="text-lg font-semibold text-gray-700 mb-2">No habits yet</h3>
                    <p className="text-sm text-gray-400 mb-6">Start building good habits today</p>
                    <button
                        onClick={openCreate}
                        className="px-6 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition"
                    >
                        Create your first habit
                    </button>
                </div>
            )}

            {/* Archived */}
            {archivedHabits.length > 0 && (
                <div className="mt-8">
                    <button
                        onClick={() => setShowArchived(!showArchived)}
                        className="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 mb-3"
                    >
                        <svg
                            className={`w-4 h-4 transition-transform ${showArchived ? "rotate-90" : ""}`}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>Archived</span>
                        <span className="text-xs bg-gray-100 px-2 py-0.5 rounded-full">
                            {archivedHabits.length}
                        </span>
                    </button>
                    {showArchived && (
                        <div className="space-y-2">
                            {archivedHabits.map((habit) => (
                                <div
                                    key={habit.id}
                                    className="bg-gray-50 rounded-2xl border border-gray-200 p-4 flex items-center gap-4 opacity-60"
                                >
                                    <span className="text-2xl flex-shrink-0">{habit.icon}</span>
                                    <div className="flex-1 min-w-0">
                                        <div className="font-medium text-gray-700 text-sm truncate">
                                            {habit.name}
                                        </div>
                                        <div className="text-xs text-gray-400">{habit.frequency_label}</div>
                                    </div>
                                    <button
                                        onClick={() => archiveHabit(habit)}
                                        className="text-xs text-gray-400 hover:text-gray-600 px-3 py-1 rounded-lg hover:bg-gray-200 transition"
                                    >
                                        Unarchive
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* ===== CREATE/EDIT MODAL ===== */}
            {modalOpen && form && (
                <>
                    <div className="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" onClick={() => setModalOpen(false)}>
                        <div
                            className="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto"
                            onClick={(e) => e.stopPropagation()}
                        >
                            {/* Header */}
                            <div className="flex items-center justify-between p-5 pb-3">
                                <h3 className="text-lg font-semibold text-gray-900">
                                    {modalMode === "create" ? "New Habit" : "Edit Habit"}
                                </h3>
                                <button onClick={() => setModalOpen(false)} className="p-1 hover:bg-gray-100 rounded-lg transition">
                                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div className="px-5 pb-5 space-y-4">
                                {/* Icon + Name */}
                                <div className="flex items-center gap-3">
                                    <div className="relative flex-shrink-0">
                                        <button
                                            onClick={() => setIconPickerOpen(!iconPickerOpen)}
                                            className="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-2xl hover:bg-gray-100 transition"
                                        >
                                            {form.icon}
                                        </button>
                                        {iconPickerOpen && (
                                            <IconPicker
                                                value={form.icon}
                                                onChange={(ic) => updateForm("icon", ic)}
                                                onClose={() => setIconPickerOpen(false)}
                                            />
                                        )}
                                    </div>
                                    <input
                                        type="text"
                                        value={form.name}
                                        onChange={(e) => updateForm("name", e.target.value)}
                                        placeholder="Habit name"
                                        className="flex-1 text-base font-medium text-gray-900 placeholder-gray-300 bg-transparent border-0 p-0 outline-none focus:ring-0"
                                    />
                                </div>

                                {/* Frequency */}
                                <div>
                                    <label className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Frequency</label>
                                    <select
                                        value={form.frequency_type}
                                        onChange={(e) => updateForm("frequency_type", e.target.value)}
                                        className="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                    >
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="interval">Every X days</option>
                                    </select>
                                </div>

                                {/* Goal */}
                                <div>
                                    <label className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Goal</label>
                                    <select
                                        value={form.goal_type}
                                        onChange={(e) => updateForm("goal_type", e.target.value)}
                                        className="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                    >
                                        <option value="boolean">Achieve it all</option>
                                        <option value="count">Reach a certain amount</option>
                                    </select>
                                    {form.goal_type === "count" && (
                                        <div className="mt-2 space-y-2 bg-gray-50 rounded-xl p-3">
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs text-gray-500">I do this</span>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    value={form.goal_config?.target || 1}
                                                    onChange={(e) => updateGoalConfig("target", parseInt(e.target.value) || 1)}
                                                    className="w-16 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:border-brand-500 focus:ring-0 outline-none bg-white"
                                                />
                                                <span className="text-xs text-gray-500">times</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs text-gray-500">Unit:</span>
                                                <input
                                                    type="text"
                                                    value={form.goal_config?.unit || ""}
                                                    onChange={(e) => updateGoalConfig("unit", e.target.value)}
                                                    placeholder="e.g. pages, reps, glasses"
                                                    className="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-0 outline-none bg-white"
                                                />
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs text-gray-500">Each check-in adds</span>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    value={form.goal_config?.increment || 1}
                                                    onChange={(e) => updateGoalConfig("increment", parseInt(e.target.value) || 1)}
                                                    className="w-16 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:border-brand-500 focus:ring-0 outline-none bg-white"
                                                />
                                                <span className="text-xs text-gray-500">to count</span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Start Date */}
                                <div>
                                    <label className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Start Date</label>
                                    <input
                                        type="date"
                                        value={form.start_date || todayStr}
                                        onChange={(e) => updateForm("start_date", e.target.value)}
                                        className="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                    />
                                </div>

                                {/* Goal Days */}
                                <div>
                                    <label className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Goal Days</label>
                                    <select
                                        value={form.goal_days || 0}
                                        onChange={(e) => updateForm("goal_days", parseInt(e.target.value))}
                                        className="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                    >
                                        {GOAL_DAY_OPTIONS.map((opt) => (
                                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                                        ))}
                                    </select>
                                </div>

                                {/* Section */}
                                <div>
                                    <label className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Section</label>
                                    <select
                                        value={form.section || "Others"}
                                        onChange={(e) => updateForm("section", e.target.value)}
                                        className="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 bg-white"
                                    >
                                        {allSections().map((s) => (
                                            <option key={s} value={s}>{s}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="flex items-center justify-between p-5 pt-3 border-t border-gray-100">
                                {modalMode === "edit" ? (
                                    <button onClick={deleteHabit} className="text-sm text-red-500 hover:text-red-600 font-medium transition">
                                        Delete habit
                                    </button>
                                ) : (
                                    <div />
                                )}
                                <button
                                    onClick={saveHabit}
                                    disabled={!form.name?.trim()}
                                    className="px-6 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition disabled:opacity-40"
                                >
                                    {modalMode === "create" ? "Create" : "Save"}
                                </button>
                            </div>
                        </div>
                    </div>
                </>
            )}

            {/* ===== DETAIL PANEL ===== */}
            {detailOpen && detailHabit && (
                <>
                    <div
                        className="fixed inset-0 bg-black/20 z-40"
                        onClick={() => setDetailOpen(false)}
                    />
                    <div className="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl z-50 flex flex-col border-l border-gray-200">
                        <div className="flex items-center justify-between p-5 border-b border-gray-100">
                            <div className="flex items-center gap-3">
                                <span className="text-2xl">{detailHabit.icon}</span>
                                <div>
                                    <h3 className="font-semibold text-gray-900">{detailHabit.name}</h3>
                                    <p className="text-xs text-gray-400">{detailHabit.frequency_label}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-1">
                                <button
                                    onClick={() => openEdit(detailHabit)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition"
                                    title="Edit"
                                >
                                    <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    onClick={() => setDetailOpen(false)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition"
                                >
                                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div className="flex-1 overflow-y-auto p-5 space-y-6">
                            {/* 3x2 Stats Grid */}
                            <div className="grid grid-cols-3 gap-3">
                                <div className="bg-brand-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-brand-600">{detailStats?.monthly_checkins ?? 0}</div>
                                    <div className="text-[10px] text-brand-500 mt-0.5">Monthly check-ins</div>
                                </div>
                                <div className="bg-yellow-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-yellow-600">{detailStats?.total_checkins ?? 0}</div>
                                    <div className="text-[10px] text-yellow-500 mt-0.5">Total check-ins</div>
                                </div>
                                <div className="bg-purple-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-purple-600">{detailStats?.monthly_rate ?? 0}%</div>
                                    <div className="text-[10px] text-purple-500 mt-0.5">Monthly rate</div>
                                </div>
                                <div className="bg-orange-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-orange-600">{detailHabit.streak ?? 0}</div>
                                    <div className="text-[10px] text-orange-500 mt-0.5">Current streak</div>
                                </div>
                                <div className="bg-emerald-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-emerald-600">{detailStats?.monthly_completion ?? 0}</div>
                                    <div className="text-[10px] text-emerald-500 mt-0.5">Monthly completion</div>
                                </div>
                                <div className="bg-rose-50 rounded-xl p-3 text-center">
                                    <div className="text-xl font-bold text-rose-600">{detailStats?.total_completion ?? 0}</div>
                                    <div className="text-[10px] text-rose-500 mt-0.5">Total completion</div>
                                </div>
                            </div>

                            {/* Monthly Calendar */}
                            <div>
                                <div className="flex items-center justify-between mb-3">
                                    <h4 className="text-sm font-semibold text-gray-700">{detailMonthLabel()}</h4>
                                    <div className="flex gap-1">
                                        <button
                                            onClick={() => {
                                                const m = detailMonth === 0 ? 11 : detailMonth - 1;
                                                const y = detailMonth === 0 ? detailYear - 1 : detailYear;
                                                setDetailMonth(m);
                                                setDetailYear(y);
                                                loadDetailStats(detailHabit, m, y);
                                            }}
                                            className="p-1 hover:bg-gray-100 rounded transition"
                                        >
                                            <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button
                                            onClick={() => {
                                                const m = detailMonth === 11 ? 0 : detailMonth + 1;
                                                const y = detailMonth === 11 ? detailYear + 1 : detailYear;
                                                setDetailMonth(m);
                                                setDetailYear(y);
                                                loadDetailStats(detailHabit, m, y);
                                            }}
                                            className="p-1 hover:bg-gray-100 rounded transition"
                                        >
                                            <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div className="grid grid-cols-7 gap-1 text-center text-[10px] text-gray-400 mb-1">
                                    {DAY_NAMES_SHORT.map((d) => (<span key={d}>{d}</span>))}
                                </div>
                                <div className="grid grid-cols-7 gap-1">
                                    {Array.from({ length: detailCalendarBlanks() }).map((_, i) => <div key={`b${i}`} />)}
                                    {detailCalendarDays().map((d) => (
                                        <button
                                            key={d.date}
                                            onClick={() => {
                                                setSelectedDate(d.date);
                                                loadSelectedDateNote();
                                            }}
                                            className={`w-full aspect-square rounded-lg text-xs font-medium flex items-center justify-center transition ${
                                                !isInHabitRange(detailHabit, d.date)
                                                    ? "bg-gray-50 text-gray-300 cursor-not-allowed"
                                                    : selectedDate === d.date
                                                      ? "bg-brand-600 text-white ring-2 ring-brand-300"
                                                      : isCompleted(detailHabit, d.date)
                                                        ? "bg-brand-500 text-white"
                                                        : d.isToday
                                                          ? "bg-brand-50 text-brand-600 ring-1 ring-brand-300"
                                                          : "bg-gray-50 text-gray-600 hover:bg-gray-100"
                                            }`}
                                        >
                                            {d.day}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Trend Chart (count goals only) */}
                            {detailHabit.goal_type === "count" && detailStats?.monthly_checkins_data?.length > 0 && (
                                <div>
                                    <div className="flex items-center gap-2 mb-3">
                                        <h4 className="text-sm font-semibold text-gray-700">Daily Goals</h4>
                                        <span className="text-xs text-gray-400">(Count)</span>
                                    </div>
                                    <div className="relative">
                                        <button
                                            onClick={() => chartPage > 0 && setChartPage((p) => p - 1)}
                                            className={`absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1 bg-white/80 hover:bg-white rounded-lg shadow-sm border border-gray-200 transition ${chartPage === 0 ? "opacity-30 pointer-events-none" : ""}`}
                                        >
                                            <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <div className="overflow-hidden mx-8 rounded-xl border border-gray-100 bg-gray-50/50">
                                            <div className="px-4 pt-3 pb-1 overflow-x-auto" style={{ scrollbarWidth: "none" }}>
                                                <svg width={chartSvgWidth()} height="140" viewBox={`0 0 ${chartSvgWidth()} 140`} className="block">
                                                    {chartYTicks().map((tick) => (
                                                        <g key={`yt${tick}`}>
                                                            <line x1={0} y1={chartY(tick)} x2={chartSvgWidth()} y2={chartY(tick)} stroke="#e5e7eb" strokeWidth={1} strokeDasharray="3,3" />
                                                            <text x={0} y={chartY(tick) - 4} className="fill-gray-400" style={{ fontSize: 10 }}>{tick}</text>
                                                        </g>
                                                    ))}
                                                    <path d={chartLinePath()} fill="none" stroke="#14B8A6" strokeWidth={2} strokeLinejoin="round" strokeLinecap="round" />
                                                    {chartPoints().map((pt) => (
                                                        <circle key={`cp${pt.date}`} cx={pt.x} cy={pt.y} r={3.5} fill="#14B8A6" stroke="white" strokeWidth={1.5} />
                                                    ))}
                                                    {chartPoints().map((pt) => (
                                                        <text key={`cl${pt.date}`} x={pt.x} y={138} textAnchor="middle" className="fill-gray-400" style={{ fontSize: 9 }}>{pt.dayNum}</text>
                                                    ))}
                                                </svg>
                                            </div>
                                            <div className="px-4 pb-2 relative h-2">
                                                <div className="w-full h-1 bg-gray-200 rounded-full">
                                                    <div
                                                        className="h-1 bg-brand-400 rounded-full transition-all"
                                                        style={{ width: `${chartScrollWidth()}%`, marginLeft: `${chartScrollOffset()}%` }}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => chartPage < chartMaxPage() && setChartPage((p) => p + 1)}
                                            className={`absolute right-0 top-1/2 -translate-y-1/2 z-10 p-1 bg-white/80 hover:bg-white rounded-lg shadow-sm border border-gray-200 transition ${chartPage >= chartMaxPage() ? "opacity-30 pointer-events-none" : ""}`}
                                        >
                                            <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Habit Log */}
                            <div>
                                <h4 className="text-sm font-semibold text-gray-700 mb-3">
                                    Habit Log on {MONTH_NAMES[detailMonth]}
                                </h4>
                                {(() => {
                                    const noteForSelectedDate = (() => {
                                        const ck = detailHabit.checkins?.find((c) => c.date === selectedDate);
                                        const statsCk = detailStats?.monthly_checkins_data?.find((c) => c.date === selectedDate);
                                        return ck?.note || statsCk?.note || "";
                                    })();
                                    if (noteForSelectedDate && isInHabitRange(detailHabit, selectedDate)) {
                                        return (
                                            <div className="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 mb-2">
                                                <div className="flex items-center gap-2 mb-1">
                                                    <svg className="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span className="text-xs font-medium text-gray-500">{formatLogDate(selectedDate)}</span>
                                                </div>
                                                <p className="text-sm text-gray-700 leading-relaxed">{noteForSelectedDate}</p>
                                            </div>
                                        );
                                    }
                                    return null;
                                })()}
                                {detailStats?.monthly_log?.length > 0 ? (
                                    <div className="space-y-2">
                                        {detailStats.monthly_log
                                            .filter((e) => e.date !== selectedDate)
                                            .map((entry) => (
                                                <div key={entry.date} className="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <span className="text-xs font-medium text-gray-500">{formatLogDate(entry.date)}</span>
                                                        {entry.count > 1 && (
                                                            <span className="text-[10px] bg-brand-100 text-brand-600 px-1.5 py-0.5 rounded-full font-medium">
                                                                ×{entry.count}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-sm text-gray-700 leading-relaxed">{entry.note}</p>
                                                </div>
                                            ))}
                                    </div>
                                ) : !(() => {
                                    const ck = detailHabit.checkins?.find((c) => c.date === selectedDate);
                                    const statsCk = detailStats?.monthly_checkins_data?.find((c) => c.date === selectedDate);
                                    return ck?.note || statsCk?.note;
                                })() ? (
                                    <p className="text-sm text-gray-400 text-center py-6">
                                        No check-in thoughts to share this month yet
                                    </p>
                                ) : null}
                            </div>

                            {/* Today */}
                            {isInHabitRange(detailHabit, selectedDate) && (
                                <div className="bg-gray-50 rounded-xl p-4">
                                    <div className="flex items-center justify-between mb-2">
                                        <h4 className="text-sm font-semibold text-gray-700">
                                            {selectedDate === todayStr ? "Today" : new Date(selectedDate + "T00:00:00").toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })}
                                        </h4>
                                        <button
                                            onClick={() => toggleHabit(detailHabit, selectedDate)}
                                            className={`px-4 py-1.5 rounded-xl text-sm font-medium transition flex items-center gap-1.5 ${
                                                isCompleted(detailHabit, selectedDate)
                                                    ? "bg-brand-500 text-white"
                                                    : "bg-white border-2 border-gray-300 text-gray-500 hover:border-brand-400"
                                            }`}
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {isCompleted(detailHabit, selectedDate) ? "Done!" : "Mark done"}
                                        </button>
                                    </div>
                                    <div className="text-xs text-gray-400 mb-3">{detailHabit.goal_label}</div>
                                    <textarea
                                        value={todayNote}
                                        onChange={(e) => setTodayNote(e.target.value)}
                                        onBlur={saveTodayNote}
                                        placeholder="Add a thought about this day's check-in..."
                                        className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-0 outline-none resize-none bg-white"
                                        rows={2}
                                    />
                                </div>
                            )}

                            {/* Goal */}
                            <div className="bg-gray-50 rounded-xl p-4">
                                <h4 className="text-sm font-semibold text-gray-700 mb-2">Goal</h4>
                                <div className="text-sm text-gray-600">{detailHabit.goal_label}</div>
                                <div className="text-xs text-gray-400 mt-1">
                                    Started {detailHabit.start_date || "today"}
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
