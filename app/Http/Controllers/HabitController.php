<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HabitController extends Controller
{
    public function index()
    {
        $habits = Habit::where('user_id', Auth::id())
            ->with('checkins')
            ->orderBy('position')
            ->get()
            ->map(fn($h) => [
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
                'checkins' => $h->checkins->map(fn($c) => [
                    'date' => $c->date->format('Y-m-d'),
                    'completed' => $c->completed,
                    'count' => $c->count,
                    'note' => $c->note,
                ])->toArray(),
                'goal_days' => $h->goal_days,
                'reminders' => $h->reminders,
            ]);

        return Inertia::render('Habits', ['habits' => $habits]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'frequency_type' => 'required|string|in:daily,weekly,interval',
            'frequency_config' => 'nullable|array',
            'goal_type' => 'required|string|in:boolean,count',
            'goal_config' => 'nullable|array',
            'start_date' => 'nullable|date',
            'goal_days' => 'nullable|integer',
            'section' => 'nullable|string',
            'reminders' => 'nullable|array',
            'auto_popup' => 'nullable|boolean',
        ]);

        $habit = Habit::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'icon' => $request->icon ?? '🎯',
            'frequency_type' => $request->frequency_type,
            'frequency_config' => $request->frequency_config,
            'goal_type' => $request->goal_type,
            'goal_config' => $request->goal_config,
            'start_date' => $request->start_date ?? now()->toDateString(),
            'goal_days' => $request->goal_days ?? 0,
            'section' => $request->section ?? 'Others',
            'reminders' => $request->reminders ?? [],
            'auto_popup' => $request->boolean('auto_popup'),
        ]);

        return response()->json($habit);
    }

    public function update(Request $request, Habit $habit)
    {
        if ($habit->user_id !== Auth::id()) abort(403);

        $habit->update($request->only([
            'name', 'icon', 'frequency_type', 'frequency_config',
            'goal_type', 'goal_config', 'start_date', 'goal_days',
            'section', 'reminders', 'auto_popup', 'position',
        ]));

        return response()->json($habit);
    }

    public function destroy(Habit $habit)
    {
        if ($habit->user_id !== Auth::id()) abort(403);
        $habit->delete();
        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, Habit $habit)
    {
        $date = $request->date ?? now()->toDateString();
        $note = $request->input('note');
        $checkin = $habit->checkins()->where('date', $date)->first();

        // Note-only update: don't toggle or increment
        if ($request->has('note') && !$request->has('toggle')) {
            if ($checkin) {
                $checkin->note = $note;
                $checkin->save();
            } else {
                $checkin = HabitCheckin::create([
                    'habit_id' => $habit->id,
                    'date' => $date,
                    'completed' => false,
                    'count' => 0,
                    'note' => $note,
                ]);
            }
            return response()->json([
                'completed' => $checkin->completed,
                'count' => $checkin->count,
                'note' => $checkin->note,
                'date' => $date,
            ]);
        }

        if ($checkin) {
            if ($habit->goal_type === 'count') {
                $target = $habit->goal_config['target'] ?? 1;
                if ($checkin->count >= $target) {
                    return response()->json([
                        'completed' => true,
                        'count' => $checkin->count,
                        'note' => $checkin->note,
                        'date' => $date,
                    ]);
                }
                $increment = $habit->goal_config['increment'] ?? 1;
                $checkin->count += $increment;
                $checkin->completed = $checkin->count >= $target;
                if ($note !== null) $checkin->note = $note;
                $checkin->save();
            } else {
                $checkin->delete();
                return response()->json(['completed' => false, 'date' => $date]);
            }
        } else {
            $checkin = HabitCheckin::create([
                'habit_id' => $habit->id,
                'date' => $date,
                'completed' => true,
                'count' => $habit->goal_type === 'count' ? ($habit->goal_config['increment'] ?? 1) : 1,
                'note' => $note,
            ]);
        }

        return response()->json([
            'completed' => $checkin->completed ?? true,
            'count' => $checkin->count,
            'note' => $checkin->note,
            'date' => $date,
        ]);
    }

    public function archive(Habit $habit)
    {
        $habit->update(['is_archived' => !$habit->is_archived]);
        return response()->json(['archived' => $habit->is_archived]);
    }

    public function stats(Request $request, Habit $habit)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $startOfMonth = now()->year($year)->month($month)->startOfMonth();
        $endOfMonth = now()->year($year)->month($month)->endOfMonth();
        $now = now()->year($year)->month($month);

        $totalCheckins = $habit->checkins()->where('completed', true)->count();
        $monthlyCheckins = $habit->checkins()->where('completed', true)
            ->where('date', '>=', $startOfMonth)->count();
        $daysInMonth = $now->daysInMonth;
        $monthlyRate = $daysInMonth > 0 ? round(($monthlyCheckins / $daysInMonth) * 100) : 0;

        $monthlyCompletion = $habit->checkins()
            ->where('date', '>=', $startOfMonth)->sum('count');
        $totalCompletion = $habit->checkins()->sum('count');

        $monthlyCheckinsData = $habit->checkins()
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->get()
            ->map(fn($c) => [
                'date' => $c->date->format('Y-m-d'),
                'completed' => $c->completed,
                'count' => $c->count,
                'note' => $c->note,
            ]);

        $monthlyLog = $habit->checkins()
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->where('completed', true)
            ->whereNotNull('note')
            ->where('note', '!=', '')
            ->orderByDesc('date')
            ->get()
            ->map(fn($c) => [
                'date' => $c->date->format('Y-m-d'),
                'count' => $c->count,
                'note' => $c->note,
            ]);

        return response()->json([
            'monthly_checkins' => $monthlyCheckins,
            'total_checkins' => $totalCheckins,
            'monthly_rate' => $monthlyRate,
            'streak' => $habit->streak,
            'monthly_completion' => $monthlyCompletion,
            'total_completion' => $totalCompletion,
            'monthly_checkins_data' => $monthlyCheckinsData,
            'monthly_log' => $monthlyLog,
        ]);
    }
}
