<x-app-layout>
<div x-data="calendarApp('{{ $view }}', '{{ $current->format('Y-m-d') }}', {{ $tasks }})" class="h-full flex flex-col">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <div class="flex items-center gap-3">
            <button @click="goToday()" class="text-xs font-medium border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition text-gray-600">Today</button>
            <div class="flex items-center gap-1">
                <button @click="goPrev()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="goNext()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
            <h2 class="text-lg font-semibold text-gray-900" x-text="headerLabel"></h2>
        </div>
        <div class="flex items-center bg-gray-100 rounded-lg p-0.5">
            <template x-for="v in views" :key="v">
                <button @click="switchView(v)"
                    class="px-3 py-1 text-xs font-medium rounded-md transition capitalize"
                    :class="currentView === v ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    x-text="v.replace('-', ' ')"></button>
            </template>
        </div>
    </div>

    {{-- Calendar Content --}}
    <div class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

        {{-- Year View --}}
        <div x-show="currentView === 'year'" class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-4 gap-4">
                <template x-for="m in 12" :key="m">
                    <div class="border border-gray-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-gray-700 mb-2" x-text="monthName(m-1)"></p>
                        <div class="grid grid-cols-7 gap-px text-center">
                            <template x-for="d in ['S','M','T','W','T','F','S']">
                                <div class="text-[8px] text-gray-400" x-text="d"></div>
                            </template>
                            <template x-for="(day, idx) in yearDays(m-1)" :key="idx">
                                <div class="text-[9px] py-0.5 rounded cursor-pointer hover:bg-brand-50 transition"
                                     :class="{
                                         'bg-brand-500 text-white font-bold': day && isSameDay(currentYear, m-1, day),
                                         'font-semibold text-gray-700': day && hasTaskOnDate(fmtDate(currentYear, m-1, day)),
                                         'text-gray-400': !day
                                     }"
                                     @click="day && goToDate(fmtDate(currentYear, m-1, day))"
                                     x-text="day || ''"></div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Month View --}}
        <div x-show="currentView === 'month'" class="flex-1 flex flex-col min-h-0">
            <div class="grid grid-cols-7 border-b border-gray-100">
                <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
                    <div class="text-xs font-semibold text-gray-500 py-2 text-center" x-text="d"></div>
                </template>
            </div>
            <div class="grid grid-cols-7 flex-1 min-h-0" :style="'grid-template-rows: repeat(' + monthWeeks + ', 1fr)'">
                <template x-for="(cell, idx) in monthCells" :key="idx">
                    <div class="border-r border-b border-gray-100 p-1 overflow-hidden cursor-pointer hover:bg-gray-50/50 transition"
                         :class="{ 'bg-gray-50/30': !cell.currentMonth }"
                         @click="goToDate(cell.date)">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs w-6 h-6 flex items-center justify-center rounded-full"
                                  :class="{
                                      'bg-brand-500 text-white font-semibold': cell.isToday,
                                      'text-gray-700': cell.currentMonth && !cell.isToday,
                                      'text-gray-300': !cell.currentMonth
                                  }" x-text="cell.day"></span>
                        </div>
                        <div class="space-y-0.5">
                            <template x-for="task in tasksOnDate(cell.date).slice(0, 3)" :key="task.id">
                                <div class="text-[10px] px-1.5 py-0.5 rounded truncate cursor-pointer hover:opacity-80 transition"
                                     :style="'background-color:' + task.project_color + '20; color:' + task.project_color"
                                     @click.stop="openEditPanel(task)"
                                     x-text="task.title"></div>
                            </template>
                            <div x-show="tasksOnDate(cell.date).length > 3"
                                 class="text-[9px] text-gray-400 px-1"
                                 x-text="'+' + (tasksOnDate(cell.date).length - 3) + ' more'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Week View --}}
        <div x-show="currentView === 'week'" class="flex-1 flex flex-col min-h-0">
            <div class="grid grid-cols-8 border-b border-gray-100">
                <div class="text-xs text-gray-400 py-2 px-2"></div>
                <template x-for="day in weekDays" :key="day.date">
                    <div class="text-center py-2 border-l border-gray-100">
                        <div class="text-[10px] font-semibold text-gray-500 uppercase" x-text="day.short"></div>
                        <div class="text-lg font-bold mt-0.5"
                             :class="day.isToday ? 'text-brand-500' : 'text-gray-800'"
                             x-text="day.num"></div>
                    </div>
                </template>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-8 min-h-[600px]">
                    <div class="border-r border-gray-100">
                        <template x-for="h in 24" :key="h">
                            <div class="h-12 border-b border-gray-50 px-2 flex items-start pt-0.5">
                                <span class="text-[10px] text-gray-400" x-text="String(h-1).padStart(2,'0') + ':00'"></span>
                            </div>
                        </template>
                    </div>
                    <template x-for="day in weekDays" :key="day.date">
                        <div class="border-l border-gray-100 relative">
                            <template x-for="h in 24" :key="h">
                                <div class="h-12 border-b border-gray-50"></div>
                            </template>
                            <template x-for="(task, tIdx) in tasksOnDate(day.date)" :key="task.id">
                                <div class="absolute left-0.5 right-0.5 px-1.5 py-1 rounded text-[10px] cursor-pointer hover:opacity-80 transition truncate z-10"
                                     :style="'top:' + (8 + tIdx) * 48 + 'px; background-color:' + task.project_color + '20; color:' + task.project_color"
                                     @click="openEditPanel(task)"
                                     x-text="task.title"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Day View --}}
        <div x-show="currentView === 'day'" class="flex-1 flex flex-col min-h-0">
            <div class="border-b border-gray-100 px-4 py-3">
                <p class="text-sm font-semibold text-gray-900" x-text="dayViewLabel"></p>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-[60px_1fr] min-h-[600px]">
                    <div>
                        <template x-for="h in 24" :key="h">
                            <div class="h-12 border-b border-gray-50 px-2 flex items-start pt-0.5">
                                <span class="text-[10px] text-gray-400" x-text="String(h-1).padStart(2,'0') + ':00'"></span>
                            </div>
                        </template>
                    </div>
                    <div class="relative border-l border-gray-100">
                        <template x-for="h in 24" :key="h">
                            <div class="h-12 border-b border-gray-50"></div>
                        </template>
                        <template x-for="(task, tIdx) in tasksOnDate(currentDate)" :key="task.id">
                            <div class="absolute left-1 right-1 px-2 py-1.5 rounded text-xs cursor-pointer hover:opacity-80 transition z-10"
                                 :style="'top:' + (9 + tIdx) * 48 + 'px; background-color:' + task.project_color + '20; color:' + task.project_color"
                                 @click="openEditPanel(task)">
                                <span class="font-medium" x-text="task.title"></span>
                                <span class="text-[10px] opacity-60 ml-1" x-text="task.project_name"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Agenda View --}}
        <div x-show="currentView === 'agenda'" class="flex-1 overflow-y-auto">
            <template x-for="week in agendaWeeks" :key="week.label">
                <div class="border-b border-gray-100">
                    <div class="px-4 py-2 bg-gray-50 sticky top-0">
                        <span class="text-xs font-semibold text-gray-600" x-text="week.label"></span>
                    </div>
                    <template x-for="day in week.days" :key="day.date">
                        <div x-show="day.tasks.length > 0" class="px-4 py-2 border-b border-gray-50 last:border-0">
                            <div class="flex items-start gap-3">
                                <div class="w-10 text-center flex-shrink-0">
                                    <div class="text-[10px] text-gray-400 uppercase" x-text="day.short"></div>
                                    <div class="text-sm font-bold" :class="day.isToday ? 'text-brand-500' : 'text-gray-800'" x-text="day.num"></div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <template x-for="task in day.tasks" :key="task.id">
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition"
                                             @click="openEditPanel(task)">
                                            <div class="w-2 h-2 rounded-full flex-shrink-0" :style="'background-color:' + task.project_color"></div>
                                            <span class="text-xs" :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-700'" x-text="task.title"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <div x-show="agendaWeeks.length === 0" class="p-8 text-center text-gray-400 text-sm">
                No tasks in the next 3 weeks
            </div>
        </div>

        {{-- Multi-Day View (7 days) --}}
        <div x-show="currentView === 'multi-day'" class="flex-1 flex flex-col min-h-0">
            <div class="grid border-b border-gray-100" :style="'grid-template-columns: repeat(' + multiDayCols + ', 1fr)'">
                <template x-for="day in multiDays" :key="day.date">
                    <div class="text-center py-2 border-r border-gray-100 last:border-r-0">
                        <div class="text-[10px] font-semibold text-gray-500 uppercase" x-text="day.short"></div>
                        <div class="text-lg font-bold mt-0.5"
                             :class="day.isToday ? 'text-brand-500' : 'text-gray-800'"
                             x-text="day.num"></div>
                    </div>
                </template>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="grid min-h-[500px]" :style="'grid-template-columns: repeat(' + multiDayCols + ', 1fr)'">
                    <template x-for="day in multiDays" :key="day.date">
                        <div class="border-r border-gray-100 last:border-r-0 p-1.5 space-y-1">
                            <template x-for="task in tasksOnDate(day.date)" :key="task.id">
                                <div class="px-2 py-1.5 rounded text-[11px] cursor-pointer hover:opacity-80 transition"
                                     :style="'background-color:' + task.project_color + '15; color:' + task.project_color"
                                     @click="openEditPanel(task)">
                                    <span class="font-medium" x-text="task.title"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Multi-Week View (3 weeks) --}}
        <div x-show="currentView === 'multi-week'" class="flex-1 flex flex-col min-h-0">
            <div class="grid grid-cols-7 border-b border-gray-100">
                <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
                    <div class="text-xs font-semibold text-gray-500 py-2 text-center" x-text="d"></div>
                </template>
            </div>
            <div class="grid grid-cols-7 flex-1 min-h-0" style="grid-template-rows: repeat(3, 1fr)">
                <template x-for="(cell, idx) in multiWeekCells" :key="idx">
                    <div class="border-r border-b border-gray-100 p-1 overflow-hidden cursor-pointer hover:bg-gray-50/50 transition"
                         :class="{ 'bg-gray-50/30': !cell.currentMonth }"
                         @click="goToDate(cell.date)">
                        <span class="text-[10px] w-5 h-5 flex items-center justify-center rounded-full mb-0.5"
                              :class="{
                                  'bg-brand-500 text-white font-semibold': cell.isToday,
                                  'text-gray-700': cell.currentMonth && !cell.isToday,
                                  'text-gray-300': !cell.currentMonth
                              }" x-text="cell.day"></span>
                        <div class="space-y-px">
                            <template x-for="task in tasksOnDate(cell.date).slice(0, 2)" :key="task.id">
                                <div class="text-[9px] px-1 py-0.5 rounded truncate cursor-pointer hover:opacity-80 transition"
                                     :style="'background-color:' + task.project_color + '20; color:' + task.project_color"
                                     @click.stop="openEditPanel(task)"
                                     x-text="task.title"></div>
                            </template>
                            <div x-show="tasksOnDate(cell.date).length > 2"
                                 class="text-[8px] text-gray-400 px-1"
                                 x-text="'+' + (tasksOnDate(cell.date).length - 2)"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Edit Panel (shared) --}}
    <div x-show="editOpen" x-cloak @click="editOpen = false"
         class="fixed inset-0 bg-black/30 z-40"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
    <div x-show="editOpen" x-cloak
         class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900" x-text="editTask.title"></h2>
            <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4 flex-1 overflow-y-auto">
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Status</label>
                <span class="text-sm px-2 py-1 rounded-full"
                      :class="editTask.status === 'done' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                      x-text="editTask.status === 'done' ? 'Completed' : 'Pending'"></span>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Priority</label>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" :class="{'text-red-500': editTask.priority === 'high', 'text-yellow-500': editTask.priority === 'medium', 'text-green-500': editTask.priority === 'low'}" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                    <span class="text-sm capitalize text-gray-700" x-text="editTask.priority"></span>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Due Date</label>
                <span class="text-sm text-gray-700" x-text="editTask.due_date"></span>
            </div>
            <div x-show="editTask.project_name">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Project</label>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" :style="'background-color:' + editTask.project_color"></div>
                    <span class="text-sm text-gray-700" x-text="editTask.project_name"></span>
                </div>
            </div>
            <div class="pt-2 border-t border-gray-100">
                <a :href="'/dashboard?edit=' + editTask.id" class="text-sm text-brand-500 hover:text-brand-600 font-medium transition">Open full editor →</a>
            </div>

        </div>
    </div>

</div>
</x-app-layout>

<script>
function calendarApp(initialView, initialDate, tasksData) {
    return {
        currentView: initialView,
        currentDate: initialDate,
        tasks: tasksData,
        editOpen: false,
        editTask: { id: null, title: '', status: 'todo', priority: 'medium', due_date: '', project_name: '', project_color: '#8b5cf6' },
        views: ['year','month','week','day','agenda','multi-day','multi-week'],

        get currentYear() { return new Date(this.currentDate + 'T00:00:00').getFullYear(); },
        get currentMonth() { return new Date(this.currentDate + 'T00:00:00').getMonth(); },

        get headerLabel() {
            const d = new Date(this.currentDate + 'T00:00:00');
            const opts = { year: 'numeric', month: 'long' };
            if (this.currentView === 'day') return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            if (this.currentView === 'week' || this.currentView === 'agenda') {
                const end = new Date(d); end.setDate(end.getDate() + 6);
                const s = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const e = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                return s + ' – ' + e;
            }
            if (this.currentView === 'year') return d.toLocaleDateString('en-US', { year: 'numeric' });
            if (this.currentView === 'multi-day') {
                const end = new Date(d); end.setDate(end.getDate() + 6);
                const s = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const e = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                return s + ' – ' + e;
            }
            if (this.currentView === 'multi-week') {
                const end = new Date(d); end.setDate(end.getDate() + 20);
                const s = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const e = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                return s + ' – ' + e;
            }
            return d.toLocaleDateString('en-US', opts);
        },

        get todayStr() { return new Date().toISOString().slice(0, 10); },

        get dayViewLabel() {
            return new Date(this.currentDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        },

        monthName(m) { return new Date(2024, m).toLocaleString('en-US', { month: 'long' }); },

        fmtDate(y, m, d) { return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0'); },

        isSameDay(y, m, d) { return this.fmtDate(y, m, d) === this.currentDate; },

        yearDays(m) {
            const first = new Date(this.currentYear, m, 1).getDay();
            const last = new Date(this.currentYear, m + 1, 0).getDate();
            const days = [];
            for (let i = 0; i < first; i++) days.push(null);
            for (let d = 1; d <= last; d++) days.push(d);
            return days;
        },

        get monthWeeks() {
            const first = new Date(this.currentYear, this.currentMonth, 1).getDay();
            const last = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            return Math.ceil((first + last) / 7);
        },

        get monthCells() {
            const first = new Date(this.currentYear, this.currentMonth, 1);
            const startDay = first.getDay();
            const start = new Date(first); start.setDate(start.getDate() - startDay);
            const cells = [];
            const total = this.monthWeeks * 7;
            for (let i = 0; i < total; i++) {
                const d = new Date(start); d.setDate(d.getDate() + i);
                cells.push({
                    date: d.toISOString().slice(0, 10),
                    day: d.getDate(),
                    currentMonth: d.getMonth() === this.currentMonth,
                    isToday: d.toISOString().slice(0, 10) === this.todayStr,
                });
            }
            return cells;
        },

        get weekDays() {
            const start = new Date(this.currentDate + 'T00:00:00');
            start.setDate(start.getDate() - start.getDay());
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start); d.setDate(d.getDate() + i);
                days.push({
                    date: d.toISOString().slice(0, 10),
                    short: d.toLocaleDateString('en-US', { weekday: 'short' }),
                    num: d.getDate(),
                    isToday: d.toISOString().slice(0, 10) === this.todayStr,
                });
            }
            return days;
        },

        get multiDays() {
            const start = new Date(this.currentDate + 'T00:00:00');
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start); d.setDate(d.getDate() + i);
                days.push({
                    date: d.toISOString().slice(0, 10),
                    short: d.toLocaleDateString('en-US', { weekday: 'short' }),
                    num: d.getDate(),
                    isToday: d.toISOString().slice(0, 10) === this.todayStr,
                });
            }
            return days;
        },

        get multiDayCols() { return 7; },

        get agendaWeeks() {
            const start = new Date(this.currentDate + 'T00:00:00');
            start.setDate(start.getDate() - start.getDay());
            const weeks = [];
            for (let w = 0; w < 3; w++) {
                const weekStart = new Date(start); weekStart.setDate(weekStart.getDate() + w * 7);
                const label = weekStart.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + ' – ' +
                    new Date(weekStart.getTime() + 6 * 86400000).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                const days = [];
                for (let d = 0; d < 7; d++) {
                    const dd = new Date(weekStart); dd.setDate(dd.getDate() + d);
                    const dateStr = dd.toISOString().slice(0, 10);
                    days.push({
                        date: dateStr,
                        short: dd.toLocaleDateString('en-US', { weekday: 'short' }),
                        num: dd.getDate(),
                        isToday: dateStr === this.todayStr,
                        tasks: this.tasksOnDate(dateStr),
                    });
                }
                weeks.push({ label, days });
            }
            return weeks;
        },

        get multiWeekCells() {
            const start = new Date(this.currentDate + 'T00:00:00');
            start.setDate(start.getDate() - start.getDay());
            const cells = [];
            for (let i = 0; i < 21; i++) {
                const d = new Date(start); d.setDate(d.getDate() + i);
                cells.push({
                    date: d.toISOString().slice(0, 10),
                    day: d.getDate(),
                    currentMonth: d.getMonth() === new Date(this.currentDate + 'T00:00:00').getMonth(),
                    isToday: d.toISOString().slice(0, 10) === this.todayStr,
                });
            }
            return cells;
        },

        tasksOnDate(dateStr) {
            return this.tasks.filter(t => t.due_date === dateStr);
        },

        hasTaskOnDate(dateStr) {
            return this.tasks.some(t => t.due_date === dateStr);
        },

        shiftDate(days) {
            const d = new Date(this.currentDate + 'T00:00:00');
            d.setDate(d.getDate() + days);
            this.currentDate = d.toISOString().slice(0, 10);
        },

        goToday() { this.currentDate = this.todayStr; },

        goPrev() {
            const d = new Date(this.currentDate + 'T00:00:00');
            const offset = { year: -365, month: -30, week: -7, day: -1, agenda: -21, 'multi-day': -7, 'multi-week': -21 };
            d.setDate(d.getDate() + (offset[this.currentView] || -30));
            this.currentDate = d.toISOString().slice(0, 10);
        },

        goNext() {
            const d = new Date(this.currentDate + 'T00:00:00');
            const offset = { year: 365, month: 30, week: 7, day: 1, agenda: 21, 'multi-day': 7, 'multi-week': 21 };
            d.setDate(d.getDate() + (offset[this.currentView] || 30));
            this.currentDate = d.toISOString().slice(0, 10);
        },

        goToDate(dateStr) {
            this.currentDate = dateStr;
        },

        switchView(v) {
            this.currentView = v;
            const url = new URL(window.location);
            url.searchParams.set('view', v);
            url.searchParams.set('date', this.currentDate);
            window.history.pushState({}, '', url);
        },

        openEditPanel(task) {
            this.editTask = JSON.parse(JSON.stringify(task));
            this.editOpen = true;
        },
    };
}
</script>
