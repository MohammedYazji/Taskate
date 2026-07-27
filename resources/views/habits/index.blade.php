<x-app-layout>
<div x-data="habitTracker()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Habits</h1>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Habit
        </button>
    </div>

    {{-- Week Strip --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <button @click="weekOffset--" class="p-1 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-sm font-medium text-gray-700" x-text="weekLabel()"></span>
            <button @click="weekOffset++" class="p-1 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <div class="grid grid-cols-7 gap-2">
            <template x-for="day in weekDays()" :key="day.date">
                <button @click="selectedDate = day.date"
                    :class="selectedDate === day.date ? 'bg-brand-500 text-white shadow-sm' : day.isToday ? 'bg-brand-50 text-brand-600 ring-1 ring-brand-300' : 'text-gray-600 hover:bg-gray-50'"
                    class="flex flex-col items-center py-2.5 rounded-lg transition text-xs">
                    <span class="font-medium" x-text="day.short"></span>
                    <span class="text-lg font-bold mt-0.5" x-text="day.num"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-brand-500" x-text="completedToday()"></div>
            <div class="text-xs text-gray-500 mt-1" x-text="selectedDate === todayStr ? 'Done today' : 'Done on ' + formatDateShort(selectedDate)"></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-orange-500" x-text="bestStreak()"></div>
            <div class="text-xs text-gray-500 mt-1">Best streak</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-500" x-text="completionRate() + '%'"></div>
            <div class="text-xs text-gray-500 mt-1">Completion rate</div>
        </div>
    </div>

    {{-- Habit Groups --}}
    <template x-for="group in groupedHabits()" :key="group.name">
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide" x-text="group.name"></h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full" x-text="group.habits.length"></span>
            </div>
            <div class="space-y-2">
                <template x-for="habit in group.habits" :key="habit.id">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4 hover:shadow-sm transition cursor-pointer"
                         @click="openDetail(habit)">
                        <span class="text-2xl flex-shrink-0" x-text="habit.icon"></span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 text-sm truncate" x-text="habit.name"></div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs font-bold text-yellow-500" x-text="'⚡' + (habit.total_checkins || 0)"></span>
                                <span class="text-xs font-bold text-orange-500" x-text="'🔥' + (habit.streak || 0)"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Progress ring --}}
                            <div class="relative w-10 h-10" @click.stop="toggleHabit(habit, selectedDate)"
                                :class="!isInHabitRange(habit, selectedDate) ? 'opacity-40 pointer-events-none' : ''">
                                <svg class="w-10 h-10 -rotate-90" viewBox="0 0 40 40">
                                    <circle cx="20" cy="20" r="17" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                    <circle cx="20" cy="20" r="17" fill="none" :stroke="isCompleted(habit, selectedDate) ? '#f59e0b' : '#14B8A6'" stroke-width="3"
                                        stroke-linecap="round"
                                        :stroke-dasharray="106.81"
                                        :stroke-dashoffset="106.81 - (106.81 * habitProgress(habit))"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span x-show="habit.goal_type === 'count'" class="text-[10px] font-bold text-gray-700"
                                        x-text="todayCount(habit)"></span>
                                    <svg x-show="habit.goal_type !== 'count' && isCompleted(habit, selectedDate)" class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Empty state --}}
    <div x-show="habits.length === 0" class="text-center py-16">
        <div class="text-6xl mb-4">🎯</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No habits yet</h3>
        <p class="text-sm text-gray-400 mb-6">Start building good habits today</p>
        <button @click="openCreate()" class="px-6 py-2.5 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            Create your first habit
        </button>
    </div>

    {{-- Archived --}}
    <div x-show="archivedHabits().length > 0" class="mt-8">
        <button @click="showArchived = !showArchived" class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 mb-3">
            <svg class="w-4 h-4 transition-transform" :class="showArchived ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span>Archived</span>
            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full" x-text="archivedHabits().length"></span>
        </button>
        <div x-show="showArchived" class="space-y-2">
            <template x-for="habit in archivedHabits()" :key="habit.id">
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex items-center gap-4 opacity-60">
                    <span class="text-2xl flex-shrink-0" x-text="habit.icon"></span>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-700 text-sm truncate" x-text="habit.name"></div>
                        <div class="text-xs text-gray-400" x-text="habit.frequency_label"></div>
                    </div>
                    <button @click="archiveHabit(habit)" class="text-xs text-gray-400 hover:text-gray-600 px-3 py-1 rounded-lg hover:bg-gray-200 transition">Unarchive</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="modalOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="modalOpen = false">
        <div class="fixed inset-0 bg-black/40" @click="modalOpen = false"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 pb-3">
                <h3 class="text-lg font-semibold text-gray-900" x-text="modalMode === 'create' ? 'New Habit' : 'Edit Habit'"></h3>
                <button @click="modalOpen = false" class="p-1 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-5 pb-5 space-y-0">

                {{-- Row 1: Icon dropdown + Name --}}
                <div class="flex items-center gap-3 py-3 border-b border-gray-100">
                    <div x-data="{ iconPickerOpen: false }" class="relative flex-shrink-0">
                        <button @click="iconPickerOpen = !iconPickerOpen"
                            class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-xl hover:bg-gray-100 transition">
                            <span x-text="form.icon"></span>
                        </button>
                        <div x-show="iconPickerOpen" x-cloak @click.outside="iconPickerOpen = false"
                            class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-lg border border-gray-200 p-3 grid grid-cols-8 gap-1 z-50 w-72">
                            <template x-for="ic in iconList" :key="ic">
                                <button @click="form.icon = ic; iconPickerOpen = false"
                                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-lg transition" x-text="ic"></button>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-0.5">Daily Check-in</div>
                        <input x-model="form.name" type="text" placeholder="Habit name"
                            class="w-full text-base font-medium text-gray-900 placeholder-gray-300 bg-transparent border-0 p-0 outline-none focus:ring-0">
                    </div>
                </div>

                {{-- Row 2: Frequency --}}
                <div class="py-3 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Frequency</span>
                    </div>
                    <div class="flex gap-1.5 bg-gray-100 rounded-xl p-1">
                        <template x-for="freq in ['daily', 'weekly', 'interval']" :key="freq">
                            <button @click="form.frequency_type = freq; ensureFreqConfig()"
                                :class="form.frequency_type === freq ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="flex-1 py-2 rounded-lg text-sm font-medium capitalize transition"
                                x-text="freq"></button>
                        </template>
                    </div>
                    {{-- Daily sub-options: pick specific days --}}
                    <div x-show="form.frequency_type === 'daily'" x-cloak class="mt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Pick days:</span>
                            <div class="flex gap-1">
                                <template x-for="(dayName, i) in dayNamesShort" :key="i">
                                    <button @click="toggleWeekDay(dayName)"
                                        :class="(form.frequency_config.days || []).includes(dayName) ? 'bg-brand-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-200 border border-gray-200'"
                                        class="w-8 h-8 rounded-lg text-[10px] font-medium transition"
                                        x-text="dayName"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                    {{-- Weekly sub-options: how many days per week --}}
                    <div x-show="form.frequency_type === 'weekly'" x-cloak class="mt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">How many days per week?</span>
                            <div class="flex gap-1">
                                <template x-for="n in [1,2,3,4,5,6,7]" :key="n">
                                    <button @click="form.frequency_config.times_per_week = n"
                                        :class="form.frequency_config.times_per_week === n ? 'bg-brand-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-200 border border-gray-200'"
                                        class="w-7 h-7 rounded-lg text-[10px] font-medium transition"
                                        x-text="n"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                    {{-- Interval sub-options --}}
                    <div x-show="form.frequency_type === 'interval'" x-cloak class="mt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Every</span>
                            <input x-model.number="form.frequency_config.every" type="number" min="2" placeholder="3"
                                class="w-16 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:border-brand-500 focus:ring-0 outline-none">
                            <span class="text-xs text-gray-500">days</span>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Goal --}}
                <div class="py-3 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Goal</span>
                    </div>
                    <div class="flex gap-1.5 bg-gray-100 rounded-xl p-1">
                        <button @click="form.goal_type = 'boolean'"
                            :class="form.goal_type === 'boolean' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                            Achieve it all
                        </button>
                        <button @click="form.goal_type = 'count'; ensureGoalConfig()"
                            :class="form.goal_type === 'count' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                            Reach a certain amount
                        </button>
                    </div>
                    {{-- Count config --}}
                    <div x-show="form.goal_type === 'count'" x-cloak class="mt-3 bg-gray-50 rounded-xl p-3 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">I do this</span>
                            <input x-model.number="form.goal_config.target" type="number" min="1" placeholder="2"
                                class="w-14 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:border-brand-500 focus:ring-0 outline-none">
                            <span class="text-xs text-gray-500">times</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Unit:</span>
                            <input x-model="form.goal_config.unit" type="text" placeholder="e.g. times, pages, reps, glasses"
                                class="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-0 outline-none">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Each check-in adds</span>
                            <input x-model.number="form.goal_config.increment" type="number" min="1" placeholder="1"
                                class="w-14 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:border-brand-500 focus:ring-0 outline-none">
                            <span class="text-xs text-gray-500">to count</span>
                        </div>
                    </div>
                </div>

                {{-- Row 4: Start Date --}}
                <div class="py-3 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Start Date</span>
                        <div class="relative" x-data="{ calOpen: false }">
                            <button @click="calOpen = !calOpen"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span x-text="formatStartDate()"></span>
                            </button>
                            <div x-show="calOpen" x-cloak @click.outside="calOpen = false"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                class="absolute top-full right-0 mt-2 bg-white rounded-xl shadow-lg border border-gray-200 p-3 z-50">
                                <div class="flex items-center justify-between mb-2">
                                    <button @click="startCalPrev()" class="p-1 hover:bg-gray-100 rounded transition">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <span class="text-xs font-medium text-gray-700" x-text="startCalMonthLabel()"></span>
                                    <button @click="startCalNext()" class="p-1 hover:bg-gray-100 rounded transition">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-7 gap-0.5 text-center text-[9px] text-gray-400 mb-1">
                                    <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                                </div>
                                <div class="grid grid-cols-7 gap-0.5">
                                    <template x-for="b in startCalBlanks()" :key="'sb'+b"><div></div></template>
                                    <template x-for="d in startCalDays()" :key="'sd'+d.date">
                                        <button @click="form.start_date = d.date; calOpen = false"
                                            :class="form.start_date === d.date ? 'bg-brand-500 text-white' : d.isToday ? 'bg-brand-50 text-brand-600' : 'hover:bg-gray-100 text-gray-600'"
                                            class="w-7 h-7 rounded-lg text-[10px] font-medium transition"
                                            x-text="d.day"></button>
                                    </template>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <button @click="form.start_date = todayStr; calOpen = false"
                                        class="w-full text-center text-xs text-brand-500 hover:text-brand-600 font-medium">Today</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 5: Goal Days --}}
                <div class="py-3 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Goal Days</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="opt in goalDayOptions" :key="opt.value">
                            <button @click="form.goal_days = opt.value"
                                :class="form.goal_days === opt.value ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                x-text="opt.label"></button>
                        </template>
                    </div>
                </div>

                {{-- Row 6: Section --}}
                <div class="py-3 border-b border-gray-100" x-data="{ addingSection: false, newSectionName: '' }">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Section</span>
                        <button x-show="!addingSection" @click="addingSection = true; newSectionName = ''"
                            class="text-xs text-brand-500 hover:text-brand-600 font-medium transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add
                        </button>
                    </div>
                    <div x-show="!addingSection" class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="s in allSections()" :key="s">
                            <button @click="form.section = s"
                                :class="form.section === s ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                x-text="s"></button>
                        </template>
                    </div>
                    <div x-show="addingSection" class="mt-2 flex gap-2">
                        <input x-model="newSectionName" @keydown.enter.prevent="if(newSectionName.trim()){ customSectionNames.push(newSectionName.trim()); form.section = newSectionName.trim(); addingSection = false; }" @keydown.escape="addingSection = false" type="text" placeholder="Section name"
                            class="flex-1 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-0 outline-none"
                            x-init="$nextTick(() => $el.focus())">
                        <button @click="if(newSectionName.trim()){ customSectionNames.push(newSectionName.trim()); form.section = newSectionName.trim(); addingSection = false; }"
                            class="px-3 py-1.5 bg-brand-500 text-white rounded-lg text-xs font-medium hover:bg-brand-600 transition">Add</button>
                        <button @click="addingSection = false"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-500 hover:bg-gray-50 transition">Cancel</button>
                    </div>
                </div>

                {{-- Row 7: Reminder --}}
                <div class="py-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Reminder</span>
                        <button @click="addReminder()"
                            class="text-xs text-brand-500 hover:text-brand-600 font-medium transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add time
                        </button>
                    </div>
                    <div x-show="form.reminders && form.reminders.length > 0" class="space-y-1.5">
                        <template x-for="(rem, ri) in (form.reminders || [])" :key="ri">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input type="time" x-model="form.reminders[ri]"
                                    class="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-0 outline-none">
                                <button @click="form.reminders.splice(ri, 1)"
                                    class="p-1 text-gray-400 hover:text-red-500 transition rounded-lg hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div x-show="!form.reminders || form.reminders.length === 0" class="text-xs text-gray-400">
                        No reminders set
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between p-5 pt-3 border-t border-gray-100">
                <button x-show="modalMode === 'edit'" @click="deleteHabit()"
                    class="text-sm text-red-500 hover:text-red-600 font-medium transition">Delete habit</button>
                <div x-show="modalMode === 'create'" class="w-1"></div>
                <button @click="saveHabit()"
                    class="px-6 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition disabled:opacity-40"
                    :disabled="!form.name.trim()">
                    <span x-text="modalMode === 'create' ? 'Create' : 'Save'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Detail Panel --}}
    <div x-show="detailOpen" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl z-50 flex flex-col border-l border-gray-200">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="text-2xl" x-text="detailHabit?.icon"></span>
                <div>
                    <h3 class="font-semibold text-gray-900" x-text="detailHabit?.name"></h3>
                    <p class="text-xs text-gray-400" x-text="detailHabit?.frequency_label"></p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="openEdit(detailHabit)" class="p-2 hover:bg-gray-100 rounded-lg transition" title="Edit">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click="detailOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-6">
            {{-- 3x2 Stats Grid --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-brand-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-brand-600" x-text="detailStats?.monthly_checkins ?? 0"></div>
                    <div class="text-[10px] text-brand-500 mt-0.5">Monthly check-ins</div>
                </div>
                <div class="bg-yellow-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-yellow-600" x-text="detailStats?.total_checkins ?? 0"></div>
                    <div class="text-[10px] text-yellow-500 mt-0.5">Total check-ins</div>
                </div>
                <div class="bg-purple-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-purple-600" x-text="(detailStats?.monthly_rate ?? 0) + '%'"></div>
                    <div class="text-[10px] text-purple-500 mt-0.5">Monthly rate</div>
                </div>
                <div class="bg-orange-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-orange-600" x-text="detailHabit?.streak ?? 0"></div>
                    <div class="text-[10px] text-orange-500 mt-0.5">Current streak</div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-emerald-600" x-text="detailStats?.monthly_completion ?? 0"></div>
                    <div class="text-[10px] text-emerald-500 mt-0.5">Monthly completion</div>
                </div>
                <div class="bg-rose-50 rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-rose-600" x-text="detailStats?.total_completion ?? 0"></div>
                    <div class="text-[10px] text-rose-500 mt-0.5">Total completion</div>
                </div>
            </div>

            {{-- Monthly Calendar --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700" x-text="detailMonthLabel()"></h4>
                    <div class="flex gap-1">
                        <button @click="detailMonthPrev()" class="p-1 hover:bg-gray-100 rounded transition">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="detailMonthNext()" class="p-1 hover:bg-gray-100 rounded transition">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] text-gray-400 mb-1">
                    <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="blank in detailCalendarBlanks()" :key="'b'+blank">
                        <div></div>
                    </template>
                    <template x-for="d in detailCalendarDays()" :key="d.date">
                        <button @click="selectedDate = d.date; loadSelectedDateNote()"
                            :class="!isInHabitRange(detailHabit, d.date) ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : selectedDate === d.date ? 'bg-brand-600 text-white ring-2 ring-brand-300' : isCompleted(detailHabit, d.date) ? 'bg-brand-500 text-white' : d.isToday ? 'bg-brand-50 text-brand-600 ring-1 ring-brand-300' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                            class="w-full aspect-square rounded-lg text-xs font-medium flex items-center justify-center transition"
                            x-text="d.day"></button>
                    </template>
                </div>
            </div>

            {{-- Trend Chart (count goals only) --}}
            <div x-show="detailHabit?.goal_type === 'count' && detailStats?.monthly_checkins_data?.length > 0">
                <div class="flex items-center gap-2 mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">Daily Goals</h4>
                    <span class="text-xs text-gray-400">(Count)</span>
                </div>
                <div class="relative">
                    {{-- Left arrow --}}
                    <button @click="chartPage > 0 && chartPage--"
                        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1 bg-white/80 hover:bg-white rounded-lg shadow-sm border border-gray-200 transition"
                        :class="chartPage === 0 ? 'opacity-30 pointer-events-none' : ''">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    {{-- Chart area --}}
                    <div class="overflow-hidden mx-8 rounded-xl border border-gray-100 bg-gray-50/50">
                        <div class="px-4 pt-3 pb-1 overflow-x-auto" style="scrollbar-width:none" x-ref="chartScroll">
                            <svg :width="chartSvgWidth()" height="140" :viewBox="'0 0 ' + chartSvgWidth() + ' 140'" class="block">
                                {{-- Y-axis gridlines + labels --}}
                                <template x-for="tick in chartYTicks()" :key="'yt'+tick">
                                    <g>
                                        <line :x1="0" :y1="chartY(tick)" :x2="chartSvgWidth()" :y2="chartY(tick)" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="3,3"/>
                                        <text x="0" :y="chartY(tick) - 4" class="fill-gray-400" style="font-size:10px" x-text="tick"></text>
                                    </g>
                                </template>
                                {{-- Line path --}}
                                <path :d="chartLinePath()" fill="none" stroke="#14B8A6" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                                {{-- Data points --}}
                                <template x-for="pt in chartPoints()" :key="'cp'+pt.date">
                                    <circle :cx="pt.x" :cy="pt.y" r="3.5" fill="#14B8A6" stroke="white" stroke-width="1.5"/>
                                </template>
                                {{-- X-axis labels --}}
                                <template x-for="pt in chartPoints()" :key="'cl'+pt.date">
                                    <text :x="pt.x" y="138" text-anchor="middle" class="fill-gray-400" style="font-size:9px" x-text="pt.dayNum"></text>
                                </template>
                            </svg>
                        </div>
                        {{-- Scroll indicator --}}
                        <div class="px-4 pb-2 relative h-2">
                            <div class="w-full h-1 bg-gray-200 rounded-full">
                                <div class="h-1 bg-brand-400 rounded-full transition-all"
                                     :style="'width:' + chartScrollWidth() + '%; margin-left:' + chartScrollOffset() + '%'"></div>
                            </div>
                        </div>
                    </div>
                    {{-- Right arrow --}}
                    <button @click="chartPage < chartMaxPage() && chartPage++"
                        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 p-1 bg-white/80 hover:bg-white rounded-lg shadow-sm border border-gray-200 transition"
                        :class="chartPage >= chartMaxPage() ? 'opacity-30 pointer-events-none' : ''">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Habit Log --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3" x-text="'Habit Log on ' + monthName(detailMonth)"></h4>
                {{-- Selected date note --}}
                <template x-if="selectedDateNote() && isInHabitRange(detailHabit, selectedDate)">
                    <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 mb-2">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span class="text-xs font-medium text-gray-500" x-text="formatLogDate(selectedDate)"></span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed" x-text="selectedDateNote()"></p>
                    </div>
                </template>
                {{-- Monthly log entries --}}
                <template x-if="detailStats?.monthly_log?.length > 0">
                    <div class="space-y-2">
                        <template x-for="entry in (detailStats?.monthly_log || []).filter(e => e.date !== selectedDate)" :key="entry.date">
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-medium text-gray-500" x-text="formatLogDate(entry.date)"></span>
                                    <span x-show="entry.count > 1" class="text-[10px] bg-brand-100 text-brand-600 px-1.5 py-0.5 rounded-full font-medium" x-text="'×' + entry.count"></span>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed" x-text="entry.note"></p>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!selectedDateNote() && (!detailStats?.monthly_log || detailStats.monthly_log.length === 0)">
                    <p class="text-sm text-gray-400 text-center py-6">No check-in thoughts to share this month yet</p>
                </template>
            </div>

            {{-- Today --}}
            <div x-show="isInHabitRange(detailHabit, selectedDate)" class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-gray-700" x-text="selectedDate === todayStr ? 'Today' : formatDateShort(selectedDate)"></h4>
                    <button @click="toggleHabit(detailHabit, selectedDate)"
                        :class="isCompleted(detailHabit, selectedDate) ? 'bg-brand-500 text-white' : 'bg-white border-2 border-gray-300 text-gray-500 hover:border-brand-400'"
                        class="px-4 py-1.5 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="isCompleted(detailHabit, selectedDate) ? 'Done!' : 'Mark done'"></span>
                    </button>
                </div>
                <div class="text-xs text-gray-400 mb-3" x-text="detailHabit?.goal_label"></div>
                {{-- Note input --}}
                <div>
                    <textarea x-model="todayNote" x-ref="noteInput"
                        placeholder="Add a thought about this day's check-in..."
                        @blur="saveTodayNote()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-0 outline-none resize-none bg-white"
                        rows="2"></textarea>
                </div>
            </div>

            {{-- Goal --}}
            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Goal</h4>
                <div class="text-sm text-gray-600" x-text="detailHabit?.goal_label"></div>
                <div class="text-xs text-gray-400 mt-1" x-text="'Started ' + (detailHabit?.start_date || 'today')"></div>
            </div>
        </div>
    </div>

    <div x-show="detailOpen" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="detailOpen = false"
        class="fixed inset-0 bg-black/20 z-40">
    </div>

</div>

@push('scripts')
<script>
function habitTracker() {
    function fmt(d) {
        var y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0');
        return y+'-'+m+'-'+day;
    }
    function addDays(d, n) { var r = new Date(d); r.setDate(r.getDate()+n); return r; }
    function startOfWeek(d) {
        var r = new Date(d);
        var day = r.getDay();
        var diff = (day === 0 ? -6 : 1) - day;
        r.setDate(r.getDate() + diff);
        r.setHours(0,0,0,0);
        return r;
    }
    function daysInMonth(y, m) { return new Date(y, m+1, 0).getDate(); }
    function monthName(m) { return ['January','February','March','April','May','June','July','August','September','October','November','December'][m]; }
    function dayShort(d) { return ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()]; }

    var today = new Date(); today.setHours(0,0,0,0);
    var todayStr = fmt(today);
    var csrf = function() { return document.querySelector('meta[name=csrf-token]').content; };
    var api = function(url, method, body) {
        var opts = { method: method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' } };
        if (body) opts.body = JSON.stringify(body);
        return fetch(url, opts).then(function(r) { return r.json(); });
    };

    return {
        init() {
            var self = this;
            this.$watch('selectedDate', function() {
                self.loadSelectedDateNote();
            });
            this.$watch('detailOpen', function(val) {
                if (val) self.loadSelectedDateNote();
            });
        },
        habits: {{ Js::from($habits->map(fn($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'icon' => $h->icon,
            'frequency_type' => $h->frequency_type,
            'frequency_config' => $h->frequency_config,
            'frequency_label' => $h->frequency_label,
            'goal_type' => $h->goal_type,
            'goal_config' => $h->goal_config,
            'goal_label' => $h->goal_label,
            'start_date' => $h->start_date ? $h->start_date->format('Y-m-d') : '',
            'section' => $h->section,
            'is_archived' => $h->is_archived,
            'streak' => $h->streak,
            'total_checkins' => $h->total_checkins,
            'checkins' => $h->checkins->map(fn($c) => ['date' => $c->date->format('Y-m-d'), 'completed' => $c->completed, 'count' => $c->count, 'note' => $c->note])->toArray(),
            'goal_days' => $h->goal_days,
            'reminders' => $h->reminders,
        ])->toArray()) }},
        selectedDate: todayStr,
        todayStr: todayStr,
        weekOffset: 0,
        modalOpen: false,
        modalMode: 'create',
        form: { id: null, name: '', icon: '🎯', frequency_type: 'daily', frequency_config: {}, goal_type: 'boolean', goal_config: {target:1,unit:'Count',increment:1}, start_date: todayStr, section: 'Others', goal_days: 0, reminders: [], auto_popup: false },
        detailOpen: false,
        detailHabit: null,
        detailStats: null,
        detailMonth: today.getMonth(),
        detailYear: today.getFullYear(),
        showArchived: false,
        dayNamesShort: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        iconList: ['🎯','💪','🏃','📚','💧','🧘','😴','🥗','💊','🎨','✍️','🎵','🧹','🛒','💰','🌱','🐕','👶','❤️','🙏','☕','🍎','🩸','🧠','⏰','📞','💄','💇','🏋️','🚴','🏊','📖','💤','🚭'],
        defaultSections: ['Morning','Afternoon','Night','Others'],
        customSectionNames: [],
        goalDayOptions: [
            { value: 0, label: 'Forever' },
            { value: 7, label: '7 days' },
            { value: 21, label: '21 days' },
            { value: 30, label: '30 days' },
            { value: 100, label: '100 days' },
            { value: 365, label: '365 days' },
        ],
        startCalMonth: today.getMonth(),
        startCalYear: today.getFullYear(),
        chartPage: 0,
        todayNote: '',

        ensureFreqConfig() {
            if (!this.form.frequency_config) this.form.frequency_config = {};
            if (this.form.frequency_type === 'weekly' && !this.form.frequency_config.days) this.form.frequency_config.days = [];
            if (this.form.frequency_type === 'interval' && !this.form.frequency_config.every) this.form.frequency_config.every = 2;
        },

        ensureGoalConfig() {
            if (!this.form.goal_config || !this.form.goal_config.target) this.form.goal_config = { target: 1, unit: 'Count', increment: 1 };
        },

        allSections() {
            var self = this;
            var existing = this.existingSections().filter(function(s) { return self.defaultSections.indexOf(s) === -1; });
            return this.defaultSections.concat(this.customSectionNames.filter(function(s) { return self.defaultSections.indexOf(s) === -1 && existing.indexOf(s) === -1; }));
        },

        formatStartDate() {
            if (!this.form.start_date) return 'Today';
            var d = new Date(this.form.start_date + 'T00:00:00');
            var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            return days[d.getDay()] + ', ' + monthName(d.getMonth()) + ' ' + d.getDate() + ', ' + d.getFullYear();
        },

        startCalBlanks() {
            var first = new Date(this.startCalYear, this.startCalMonth, 1);
            var day = first.getDay();
            return (day === 0 ? 6 : day - 1);
        },

        startCalDays() {
            var total = daysInMonth(this.startCalYear, this.startCalMonth);
            var days = [];
            for (var i = 1; i <= total; i++) {
                var d = new Date(this.startCalYear, this.startCalMonth, i);
                days.push({ date: fmt(d), day: i, isToday: fmt(d) === todayStr });
            }
            return days;
        },

        startCalMonthLabel() { return monthName(this.startCalMonth) + ' ' + this.startCalYear; },

        startCalPrev() { this.startCalMonth--; if (this.startCalMonth < 0) { this.startCalMonth = 11; this.startCalYear--; } },

        startCalNext() { this.startCalMonth++; if (this.startCalMonth > 11) { this.startCalMonth = 0; this.startCalYear++; } },

        addReminder() {
            if (!this.form.reminders) this.form.reminders = [];
            this.form.reminders.push('09:00');
        },

        weekDays() {
            var start = addDays(startOfWeek(today), this.weekOffset * 7);
            var days = [];
            for (var i = 0; i < 7; i++) {
                var d = addDays(start, i);
                if (d > today) continue;
                days.push({ date: fmt(d), short: dayShort(d).slice(0,3), num: d.getDate(), isToday: fmt(d) === todayStr });
            }
            return days;
        },

        weekLabel() {
            var days = this.weekDays();
            if (days.length === 0) return '';
            return days[0].short + ' ' + days[0].num + ' – ' + days[days.length-1].short + ' ' + days[days.length-1].num + ', ' + days[days.length-1].date.slice(0,4);
        },

        last7Days() {
            var days = [];
            for (var i = 6; i >= 0; i--) {
                var d = addDays(today, -i);
                days.push({ date: fmt(d), short: dayShort(d).slice(0,3) });
            }
            return days;
        },

        isCompleted(habit, date) {
            if (!habit || !habit.checkins) return false;
            return habit.checkins.some(function(c) { return c.date === date && c.completed; });
        },

        formatDateLong(dateStr) {
            var d = new Date(dateStr + 'T00:00:00');
            var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            return days[d.getDay()] + ', ' + months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
        },

        formatDateShort(dateStr) {
            var d = new Date(dateStr + 'T00:00:00');
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
        },

        dayDoneCount(date) {
            var active = this.habits.filter(function(h) { return !h.is_archived; });
            var self = this;
            return active.filter(function(h) { return self.isCompleted(h, date); }).length;
        },

        dayProgress(date) {
            var active = this.habits.filter(function(h) { return !h.is_archived; });
            if (active.length === 0) return 0;
            return this.dayDoneCount(date) / active.length;
        },

        dayProgressClass(habit, date) {
            if (!habit || !habit.checkins) return 'bg-gray-100 text-gray-300';
            var ck = habit.checkins.find(function(c) { return c.date === date; });
            if (!ck) return 'bg-gray-100 text-gray-300';
            if (ck.completed) return 'bg-brand-500 text-white';
            if (habit.goal_type === 'count' && ck.count > 0) return 'bg-brand-200 text-brand-700';
            return 'bg-gray-100 text-gray-300';
        },

        dayProgressLabel(habit, date) {
            if (!habit || !habit.checkins) return '';
            var ck = habit.checkins.find(function(c) { return c.date === date; });
            if (!ck) return '';
            if (ck.completed) return '✓';
            if (habit.goal_type === 'count' && ck.count > 0) return ck.count;
            return '';
        },

        todayCount(habit) {
            if (!habit || !habit.checkins) return 0;
            var date = this.selectedDate;
            var ck = habit.checkins.find(function(c) { return c.date === date; });
            return ck ? (ck.count || 0) : 0;
        },

        habitProgress(habit) {
            if (!habit) return 0;
            if (habit.goal_type === 'boolean') return this.isCompleted(habit, this.selectedDate) ? 1 : 0;
            var target = (habit.goal_config && habit.goal_config.target) || 1;
            var count = this.todayCount(habit);
            return Math.min(count / target, 1);
        },

        completedToday() {
            var self = this;
            return this.habits.filter(function(h) { return !h.is_archived && self.isCompleted(h, self.selectedDate); }).length;
        },

        bestStreak() {
            var active = this.habits.filter(function(h) { return !h.is_archived; });
            if (active.length === 0) return 0;
            return Math.max.apply(null, active.map(function(h) { return h.streak || 0; }));
        },

        completionRate() {
            var self = this;
            var active = this.habits.filter(function(h) { return !h.is_archived; });
            if (active.length === 0) return 0;
            var done = active.filter(function(h) { return self.isCompleted(h, todayStr); }).length;
            return Math.round((done / active.length) * 100);
        },

        groupedHabits() {
            var self = this;
            var active = this.habits.filter(function(h) { return !h.is_archived && self.isInHabitRange(h, self.selectedDate); });
            var groups = {};
            active.forEach(function(h) {
                var sec = h.section || 'Others';
                if (!groups[sec]) groups[sec] = [];
                groups[sec].push(h);
            });
            return Object.keys(groups).map(function(name) { return { name: name, habits: groups[name] }; });
        },

        archivedHabits() {
            return this.habits.filter(function(h) { return h.is_archived; });
        },

        existingSections() {
            var seen = {};
            this.habits.forEach(function(h) { if (h.section) seen[h.section] = true; });
            return Object.keys(seen);
        },

        customSections() {
            var self = this;
            var existing = this.existingSections().filter(function(s) { return self.defaultSections.indexOf(s) === -1; });
            return existing.concat(this.customSectionNames.filter(function(s) { return existing.indexOf(s) === -1; }));
        },

        toggleWeekDay(dayName) {
            this.ensureFreqConfig();
            var idx = this.form.frequency_config.days.indexOf(dayName);
            if (idx >= 0) this.form.frequency_config.days.splice(idx, 1);
            else this.form.frequency_config.days.push(dayName);
        },

        openCreate() {
            this.modalMode = 'create';
            this.form = { id: null, name: '', icon: '🎯', frequency_type: 'daily', frequency_config: {}, goal_type: 'boolean', goal_config: {target:1,unit:'Count',increment:1}, start_date: todayStr, section: 'Others', goal_days: 0, reminders: [], auto_popup: false };
            this.startCalMonth = today.getMonth();
            this.startCalYear = today.getFullYear();
            this.modalOpen = true;
        },

        openEdit(habit) {
            this.modalMode = 'edit';
            this.form = JSON.parse(JSON.stringify(habit));
            if (!this.form.frequency_config) this.form.frequency_config = {};
            if (!this.form.goal_config) this.form.goal_config = {target:1,unit:'Count',increment:1};
            if (!this.form.reminders) this.form.reminders = [];
            if (this.form.start_date) {
                var sd = new Date(this.form.start_date + 'T00:00:00');
                this.startCalMonth = sd.getMonth();
                this.startCalYear = sd.getFullYear();
            } else {
                this.startCalMonth = today.getMonth();
                this.startCalYear = today.getFullYear();
            }
            this.detailOpen = false;
            this.modalOpen = true;
        },

        saveHabit() {
            if (!this.form.name.trim()) return;
            var self = this;
            var url = this.modalMode === 'create' ? '/habits' : '/habits/' + this.form.id;
            var method = this.modalMode === 'create' ? 'POST' : 'PATCH';
            api(url, method, this.form).then(function(data) {
                if (self.modalMode === 'create') { data.checkins = []; self.habits.push(data); }
                else { var idx = self.habits.findIndex(function(h) { return h.id === data.id; }); if (idx >= 0) { data.checkins = self.habits[idx].checkins; self.habits[idx] = data; } }
                self.modalOpen = false;
            });
        },

        deleteHabit() {
            if (!confirm('Delete this habit? This cannot be undone.')) return;
            var self = this;
            api('/habits/' + this.form.id, 'DELETE').then(function() {
                self.habits = self.habits.filter(function(h) { return h.id !== self.form.id; });
                self.modalOpen = false; self.detailOpen = false;
            });
        },

        archiveHabit(habit) {
            api('/habits/' + habit.id + '/archive', 'PATCH').then(function(data) { habit.is_archived = data.archived; });
        },

        isInHabitRange(habit, dateStr) {
            if (!habit || !habit.start_date) return true;
            var d = new Date(dateStr + 'T00:00:00');
            var start = new Date(habit.start_date + 'T00:00:00');
            if (d < start) return false;
            if (habit.goal_days && habit.goal_days > 0) {
                var end = new Date(start);
                end.setDate(end.getDate() + habit.goal_days - 1);
                if (d > end) return false;
            }
            if (habit.frequency_type === 'daily' && habit.frequency_config && habit.frequency_config.days && habit.frequency_config.days.length > 0) {
                var dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                var dayName = dayNames[d.getDay()];
                if (habit.frequency_config.days.indexOf(dayName) === -1) return false;
            }
            if (habit.frequency_type === 'interval' && habit.frequency_config) {
                var intervalDays = habit.frequency_config.interval || habit.frequency_config.every || 2;
                var diff = Math.floor((d - start) / (1000 * 60 * 60 * 24));
                if (diff % intervalDays !== 0) return false;
            }
            return true;
        },

        toggleHabit(habit, date) {
            if (!habit) return;
            if (!this.isInHabitRange(habit, date)) return;
            api('/habits/' + habit.id + '/toggle', 'PATCH', { date: date }).then(function(data) {
                if (!habit.checkins) habit.checkins = [];
                var idx = habit.checkins.findIndex(function(c) { return c.date === date; });
                if (data.completed === false && habit.goal_type !== 'count') {
                    habit.checkins = habit.checkins.filter(function(c) { return c.date !== date; });
                } else if (idx >= 0) {
                    habit.checkins[idx] = { date: date, completed: data.completed, count: data.count || 0, note: habit.checkins[idx].note || '' };
                } else {
                    habit.checkins.push({ date: date, completed: data.completed, count: data.count || 0 });
                }
            });
        },

        openDetail(habit) {
            this.detailHabit = habit;
            this.detailMonth = today.getMonth();
            this.detailYear = today.getFullYear();
            this.chartPage = 0;
            this.detailOpen = true;
            this.loadDetailStats();
        },

        loadDetailStats() {
            var self = this;
            var month = this.detailMonth + 1;
            var year = this.detailYear;
            api('/habits/' + self.detailHabit.id + '/stats?year=' + year + '&month=' + month, 'GET').then(function(data) {
                self.detailStats = data;
                self.chartPage = 0;
                self.loadSelectedDateNote();
            });
        },

        saveTodayNote() {
            if (!this.detailHabit) return;
            var self = this;
            var date = this.selectedDate;
            var noteText = this.todayNote;
            api('/habits/' + this.detailHabit.id + '/toggle', 'PATCH', { date: date, note: noteText }).then(function(data) {
                if (!self.detailHabit.checkins) self.detailHabit.checkins = [];
                var idx = self.detailHabit.checkins.findIndex(function(c) { return c.date === date; });
                var newCheckin = { date: date, completed: idx >= 0 ? self.detailHabit.checkins[idx].completed : data.completed, count: idx >= 0 ? self.detailHabit.checkins[idx].count : data.count, note: data.note };
                if (idx >= 0) {
                    self.detailHabit.checkins[idx] = newCheckin;
                } else {
                    self.detailHabit.checkins.push(newCheckin);
                }
            });
        },

        loadSelectedDateNote() {
            if (!this.detailHabit) return;
            var date = this.selectedDate;
            var ck = (this.detailHabit.checkins || []).find(function(c) { return c.date === date; });
            if (!ck || !ck.note) {
                var statsCk = (this.detailStats?.monthly_checkins_data || []).find(function(c) { return c.date === date; });
                if (statsCk && statsCk.note) ck = statsCk;
            }
            this.todayNote = (ck && ck.note) ? ck.note : '';
        },

        selectedDateNote() {
            if (!this.detailHabit) return '';
            var date = this.selectedDate;
            var ck = (this.detailHabit.checkins || []).find(function(c) { return c.date === date; });
            if (!ck || !ck.note) {
                var statsCk = (this.detailStats?.monthly_checkins_data || []).find(function(c) { return c.date === date; });
                if (statsCk && statsCk.note) ck = statsCk;
            }
            return (ck && ck.note) ? ck.note : '';
        },

        formatLogDate(dateStr) {
            var d = new Date(dateStr + 'T00:00:00');
            var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            return days[d.getDay()] + ', ' + monthName(d.getMonth()) + ' ' + d.getDate();
        },

        detailMonthLabel() {
            return monthName(this.detailMonth) + ' ' + this.detailYear;
        },

        detailMonthPrev() {
            this.detailMonth--;
            if (this.detailMonth < 0) { this.detailMonth = 11; this.detailYear--; }
            this.loadDetailStats();
        },

        detailMonthNext() {
            this.detailMonth++;
            if (this.detailMonth > 11) { this.detailMonth = 0; this.detailYear++; }
            this.loadDetailStats();
        },

        detailCalendarBlanks() {
            var first = new Date(this.detailYear, this.detailMonth, 1);
            var day = first.getDay();
            return (day === 0 ? 6 : day - 1);
        },

        detailCalendarDays() {
            var total = daysInMonth(this.detailYear, this.detailMonth);
            var days = [];
            for (var i = 1; i <= total; i++) {
                var d = new Date(this.detailYear, this.detailMonth, i);
                days.push({ date: fmt(d), day: i, isToday: fmt(d) === todayStr });
            }
            return days;
        },

        monthName(m) { return monthName(m); },

        // --- Chart helpers ---
        chartWindowDays: 14,

        chartFilteredData() {
            var data = (this.detailStats && this.detailStats.monthly_checkins_data) || [];
            var target = this.detailHabit?.goal_config?.target || 1;
            return data.filter(function(c) { return c.count > 0; }).map(function(c) {
                return { date: c.date, count: Math.min(c.count, target), day: parseInt(c.date.split('-')[2]) };
            }).sort(function(a, b) { return a.date.localeCompare(b.date); });
        },

        chartAllDaysInMonth() {
            var total = daysInMonth(this.detailYear, this.detailMonth);
            var days = [];
            for (var i = 1; i <= total; i++) {
                var d = new Date(this.detailYear, this.detailMonth, i);
                days.push({ date: fmt(d), day: i });
            }
            return days;
        },

        chartPageDays() {
            var all = this.chartAllDaysInMonth();
            var start = this.chartPage * this.chartWindowDays;
            return all.slice(start, start + this.chartWindowDays);
        },

        chartMaxPage() {
            var total = daysInMonth(this.detailYear, this.detailMonth);
            return Math.max(0, Math.ceil(total / this.chartWindowDays) - 1);
        },

        chartSvgWidth() { return this.chartPageDays().length * 32 + 24; },

        chartYTicks() {
            var target = this.detailHabit?.goal_config?.target || 1;
            var ticks = [];
            for (var i = 0; i <= target; i++) ticks.push(i);
            return ticks;
        },

        chartY(val) {
            var target = this.detailHabit?.goal_config?.target || 1;
            var top = 16, bottom = 118;
            if (target === 0) return bottom;
            return bottom - (val / target) * (bottom - top);
        },

        chartPoints() {
            var self = this;
            var pageDays = this.chartPageDays();
            var data = this.chartFilteredData();
            var dataMap = {};
            data.forEach(function(d) { dataMap[d.date] = d.count; });
            var pts = [];
            pageDays.forEach(function(d, i) {
                var val = dataMap[d.date];
                if (val !== undefined) {
                    pts.push({ date: d.date, dayNum: d.day, x: i * 32 + 24, y: self.chartY(val), val: val });
                }
            });
            return pts;
        },

        chartLinePath() {
            var pts = this.chartPoints();
            if (pts.length < 2) return '';
            var self = this;
            var segs = [];
            var i = 0;
            while (i < pts.length) {
                var seg = [pts[i]];
                var j = i + 1;
                while (j < pts.length) {
                    var prevDay = parseInt(pts[j-1].date.split('-')[2]);
                    var curDay = parseInt(pts[j].date.split('-')[2]);
                    if (curDay - prevDay === 1) { seg.push(pts[j]); j++; }
                    else break;
                }
                if (seg.length >= 2) {
                    var d = 'M' + seg[0].x + ',' + seg[0].y;
                    for (var k = 1; k < seg.length; k++) d += 'L' + seg[k].x + ',' + seg[k].y;
                    segs.push(d);
                }
                i = j;
            }
            return segs.join(' ');
        },

        chartScrollWidth() {
            var total = daysInMonth(this.detailYear, this.detailMonth);
            return Math.min(100, (this.chartWindowDays / total) * 100);
        },

        chartScrollOffset() {
            var total = daysInMonth(this.detailYear, this.detailMonth);
            return (this.chartPage * this.chartWindowDays / total) * 100;
        }
    }
}
</script>
@endpush
</x-app-layout>
