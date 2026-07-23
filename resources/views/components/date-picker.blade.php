@props(['taskId' => null])

<div x-data="{
        open: false,
        tab: 'date',
        selectedDate: null,
        selectedTime: null,
        reminder: 'none',
        repeat: 'none',
        allDay: false,
        durationStart: null,
        durationStartTime: null,
        durationEnd: null,
        durationEndTime: null,
        calYear: new Date().getFullYear(),
        calMonth: new Date().getMonth(),

        get calDays() {
            const first = new Date(this.calYear, this.calMonth, 1).getDay();
            const last = new Date(this.calYear, this.calMonth + 1, 0).getDate();
            const days = [];
            for (let i = 0; i < first; i++) days.push(null);
            for (let d = 1; d <= last; d++) days.push(d);
            return days;
        },
        get calLabel() {
            return new Date(this.calYear, this.calMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
        },
        prevMonth() {
            if (this.calMonth === 0) { this.calMonth = 11; this.calYear--; }
            else this.calMonth--;
        },
        nextMonth() {
            if (this.calMonth === 11) { this.calMonth = 0; this.calYear++; }
            else this.calMonth++;
        },
        isToday(d) {
            const now = new Date();
            return d === now.getDate() && this.calMonth === now.getMonth() && this.calYear === now.getFullYear();
        },
        isSelected(d) {
            if (!this.selectedDate || !d) return false;
            return this.selectedDate === this.dateStr(this.calYear, this.calMonth, d);
        },
        dateStr(y, m, d) {
            return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        },
        pickDay(d) {
            if (!d) return;
            this.selectedDate = this.dateStr(this.calYear, this.calMonth, d);
        },
        setToday() {
            const now = new Date();
            this.calYear = now.getFullYear();
            this.calMonth = now.getMonth();
            this.selectedDate = this.dateStr(this.calYear, this.calMonth, now.getDate());
            this.selectedTime = null;
        },
        setTomorrow() {
            const t = new Date(); t.setDate(t.getDate() + 1);
            this.calYear = t.getFullYear();
            this.calMonth = t.getMonth();
            this.selectedDate = this.dateStr(this.calYear, this.calMonth, t.getDate());
            this.selectedTime = null;
        },
        setNextWeek() {
            const t = new Date(); t.setDate(t.getDate() + (7 - t.getDay() + 1) || 7);
            this.calYear = t.getFullYear();
            this.calMonth = t.getMonth();
            this.selectedDate = this.dateStr(this.calYear, this.calMonth, t.getDate());
            this.selectedTime = null;
        },
        setNextMonth() {
            const t = new Date(); t.setMonth(t.getMonth() + 1, 1);
            this.calYear = t.getFullYear();
            this.calMonth = t.getMonth();
            this.selectedDate = this.dateStr(this.calYear, this.calMonth, 1);
            this.selectedTime = null;
        },
        get formattedDisplay() {
            if (!this.selectedDate) return '';
            const d = new Date(this.selectedDate + 'T00:00:00');
            const opts = { month: 'short', day: 'numeric' };
            let s = d.toLocaleDateString('en-US', opts);
            if (this.selectedTime) s += ', ' + this.selectedTime;
            return s;
        },
        timeOptions: Array.from({ length: 48 }, (_, i) => {
            const h = String(Math.floor(i / 2)).padStart(2, '0');
            const m = i % 2 === 0 ? '00' : '30';
            return h + ':' + m;
        }),
        ok() {
            $dispatch('date-picker-ok', {
                date: this.selectedDate,
                time: this.selectedTime,
                reminder: this.reminder,
                repeat: this.repeat,
                allDay: this.allDay,
                durationStart: this.durationStart,
                durationStartTime: this.durationStartTime,
                durationEnd: this.durationEnd,
                durationEndTime: this.durationEndTime,
            });
            this.open = false;
        },
        clear() {
            this.selectedDate = null;
            this.selectedTime = null;
            this.reminder = 'none';
            this.repeat = 'none';
            this.allDay = false;
            this.durationStart = null;
            this.durationStartTime = null;
            this.durationEnd = null;
            this.durationEndTime = null;
            $dispatch('date-picker-ok', {
                date: null,
                time: null,
                reminder: 'none',
                repeat: 'none',
                allDay: false,
                durationStart: null,
                durationStartTime: null,
                durationEnd: null,
                durationEndTime: null,
            });
            this.open = false;
        }
    }"
    x-on:click.outside="open = false"
    class="relative inline-block">

    <button @click="open = !open"
        class="w-full text-center text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700 hover:border-brand-400 transition">
        <span x-text="formattedDisplay || 'Set date'"></span>
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        @click.outside="open = false"
        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-80 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 overflow-hidden">

        {{-- Tabs --}}
        <div class="flex border-b border-gray-100">
            <button @click="tab = 'date'"
                class="flex-1 py-2.5 text-xs font-semibold transition"
                :class="tab === 'date' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-400 hover:text-gray-600'">
                Date
            </button>
            <button @click="tab = 'duration'"
                class="flex-1 py-2.5 text-xs font-semibold transition"
                :class="tab === 'duration' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-400 hover:text-gray-600'">
                Duration
            </button>
        </div>

        {{-- Date Tab --}}
        <div x-show="tab === 'date'" class="p-3 space-y-3">

            {{-- Section 1: Quick picks --}}
            <div class="grid grid-cols-4 gap-1.5">
                <button @click="setToday()"
                    class="px-2 py-1.5 text-[11px] font-medium rounded-lg border border-gray-200 hover:border-brand-400 hover:bg-brand-50 transition text-gray-600">
                    Today
                </button>
                <button @click="setTomorrow()"
                    class="px-2 py-1.5 text-[11px] font-medium rounded-lg border border-gray-200 hover:border-brand-400 hover:bg-brand-50 transition text-gray-600">
                    Tomorrow
                </button>
                <button @click="setNextWeek()"
                    class="px-2 py-1.5 text-[11px] font-medium rounded-lg border border-gray-200 hover:border-brand-400 hover:bg-brand-50 transition text-gray-600">
                    Next Week
                </button>
                <button @click="setNextMonth()"
                    class="px-2 py-1.5 text-[11px] font-medium rounded-lg border border-gray-200 hover:border-brand-400 hover:bg-brand-50 transition text-gray-600">
                    Next Month
                </button>
            </div>

            {{-- Section 2: Calendar --}}
            <div class="border border-gray-200 rounded-xl p-2.5">
                <div class="flex items-center justify-between mb-2">
                    <button @click="prevMonth()" class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-xs font-semibold text-gray-700" x-text="calLabel"></span>
                    <button @click="nextMonth()" class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-0.5 text-center">
                    <template x-for="d in ['S','M','T','W','T','F','S']">
                        <div class="text-[10px] font-semibold text-gray-400 py-1" x-text="d"></div>
                    </template>
                    <template x-for="(day, idx) in calDays" :key="idx">
                        <button @click="pickDay(day)"
                            class="w-8 h-8 mx-auto text-xs rounded-full flex items-center justify-center transition"
                            :class="{
                                'bg-brand-500 text-white font-semibold': day && isSelected(day),
                                'bg-brand-50 text-brand-600 font-semibold': day && isToday(day) && !isSelected(day),
                                'text-gray-700 hover:bg-gray-100': day && !isSelected(day) && !isToday(day),
                                'text-transparent cursor-default': !day
                            }"
                            x-text="day || ''"></button>
                    </template>
                </div>
            </div>

            {{-- Section 3: Time --}}
            <div class="relative">
                <button @click="$refs.timeDrop.classList.toggle('hidden')"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium border border-gray-200 rounded-lg hover:border-brand-400 transition text-gray-700">
                    <span x-text="selectedTime || 'Select time'"></span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-ref="timeDrop" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-40 overflow-y-auto">
                    <button @click="selectedTime = null; $refs.timeDrop.classList.add('hidden')"
                        class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition text-gray-500">
                        No time
                    </button>
                    <template x-for="t in timeOptions" :key="t">
                        <button @click="selectedTime = t; $refs.timeDrop.classList.add('hidden')"
                            class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition"
                            :class="selectedTime === t ? 'bg-brand-50 text-brand-500 font-semibold' : 'text-gray-700'"
                            x-text="t"></button>
                    </template>
                </div>
            </div>

            {{-- Section 4: Reminder --}}
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Reminder</label>
                <div class="grid grid-cols-5 gap-1">
                    <button @click="reminder = 'on-time'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === 'on-time' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        On time
                    </button>
                    <button @click="reminder = '5m'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '5m' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        5 min
                    </button>
                    <button @click="reminder = '30m'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '30m' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        30 min
                    </button>
                    <button @click="reminder = '1h'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '1h' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        1 hour
                    </button>
                    <button @click="reminder = '1d'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '1d' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        1 day
                    </button>
                </div>
            </div>

            {{-- Section 5: Repeat --}}
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Repeat</label>
                <div class="flex flex-wrap gap-1">
                    <template x-for="r in ['daily','weekly','monthly','yearly','every-week']" :key="r">
                        <button @click="repeat = (repeat === r ? 'none' : r)"
                            class="px-2 py-1 text-[10px] font-medium rounded-lg border transition capitalize"
                            :class="repeat === r ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'"
                            x-text="r.replace('-', ' ')"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Duration Tab --}}
        <div x-show="tab === 'duration'" class="p-3 space-y-2.5">

            {{-- Start --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-semibold text-gray-400 w-10 flex-shrink-0">Start</span>
                <input type="date" x-model="durationStart"
                    class="flex-1 text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700">
                <div class="relative" x-show="!allDay">
                    <button @click="$refs.durStartTimeDrop.classList.toggle('hidden')"
                        class="w-[72px] text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none text-gray-700 hover:border-brand-400 transition text-center">
                        <span x-text="durationStartTime || '--:--'"></span>
                    </button>
                    <div x-ref="durStartTimeDrop" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-36 overflow-y-auto">
                        <button @click="durationStartTime = null; $refs.durStartTimeDrop.classList.add('hidden')"
                            class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition text-gray-500">No time</button>
                        <template x-for="t in timeOptions" :key="'s'+t">
                            <button @click="durationStartTime = t; $refs.durStartTimeDrop.classList.add('hidden')"
                                class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition"
                                :class="durationStartTime === t ? 'bg-brand-50 text-brand-500 font-semibold' : 'text-gray-700'"
                                x-text="t"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- End --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-semibold text-gray-400 w-10 flex-shrink-0">End</span>
                <input type="date" x-model="durationEnd"
                    class="flex-1 text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700">
                <div class="relative" x-show="!allDay">
                    <button @click="$refs.durEndTimeDrop.classList.toggle('hidden')"
                        class="w-[72px] text-xs font-medium border border-gray-200 rounded-lg px-2 py-1.5 outline-none text-gray-700 hover:border-brand-400 transition text-center">
                        <span x-text="durationEndTime || '--:--'"></span>
                    </button>
                    <div x-ref="durEndTimeDrop" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-36 overflow-y-auto">
                        <button @click="durationEndTime = null; $refs.durEndTimeDrop.classList.add('hidden')"
                            class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition text-gray-500">No time</button>
                        <template x-for="t in timeOptions" :key="'e'+t">
                            <button @click="durationEndTime = t; $refs.durEndTimeDrop.classList.add('hidden')"
                                class="w-full px-3 py-1.5 text-xs text-left hover:bg-brand-50 transition"
                                :class="durationEndTime === t ? 'bg-brand-50 text-brand-500 font-semibold' : 'text-gray-700'"
                                x-text="t"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- All-day toggle --}}
            <div class="flex items-center justify-between px-1 pb-2.5 border-b border-gray-100">
                <span class="text-xs font-medium text-gray-600">All Day</span>
                <button @click="allDay = !allDay"
                    class="relative w-9 h-5 rounded-full transition-colors"
                    :class="allDay ? 'bg-brand-500' : 'bg-gray-200'">
                    <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                        :class="allDay ? 'translate-x-4' : 'translate-x-0'"></span>
                </button>
            </div>

            {{-- Reminder --}}
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Reminder</label>
                <div class="grid grid-cols-5 gap-1">
                    <button @click="reminder = 'on-time'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === 'on-time' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        On time
                    </button>
                    <button @click="reminder = '5m'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '5m' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        5 min
                    </button>
                    <button @click="reminder = '30m'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '30m' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        30 min
                    </button>
                    <button @click="reminder = '1h'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '1h' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        1 hour
                    </button>
                    <button @click="reminder = '1d'"
                        class="px-1 py-1 text-[10px] font-medium rounded-lg border transition"
                        :class="reminder === '1d' ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'">
                        1 day
                    </button>
                </div>
            </div>

            {{-- Repeat --}}
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Repeat</label>
                <div class="flex flex-wrap gap-1">
                    <template x-for="r in ['daily','weekly','monthly','yearly','every-week']" :key="r">
                        <button @click="repeat = (repeat === r ? 'none' : r)"
                            class="px-2 py-1 text-[10px] font-medium rounded-lg border transition capitalize"
                            :class="repeat === r ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-200 text-gray-600 hover:border-brand-400'"
                            x-text="r.replace('-', ' ')"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-3 py-2.5 border-t border-gray-100">
            <button @click="clear()"
                class="px-3 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Clear
            </button>
            <button @click="ok()"
                class="px-4 py-1.5 text-xs font-medium text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition">
                OK
            </button>
        </div>
    </div>
</div>
