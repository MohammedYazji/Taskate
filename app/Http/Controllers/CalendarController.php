<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'month');
        $date = $request->get('date', now()->format('Y-m-d'));
        $current = Carbon::parse($date);

        $start = match($view) {
            'year'       => $current->copy()->startOfYear(),
            'month'      => $current->copy()->startOfMonth()->startOfWeek(),
            'week'       => $current->copy()->startOfWeek(),
            'day'        => $current->copy()->startOfDay(),
            'agenda'     => $current->copy()->startOfWeek(),
            'multi-day'  => $current->copy()->startOfDay(),
            'multi-week' => $current->copy()->startOfWeek(),
            default      => $current->copy()->startOfMonth()->startOfWeek(),
        };

        $end = match($view) {
            'year'       => $current->copy()->endOfYear(),
            'month'      => $current->copy()->endOfMonth()->endOfWeek(),
            'week'       => $current->copy()->endOfWeek(),
            'day'        => $current->copy()->endOfDay(),
            'agenda'     => $current->copy()->addWeeks(3)->endOfWeek(),
            'multi-day'  => $current->copy()->addDays(6)->endOfDay(),
            'multi-week' => $current->copy()->addWeeks(3)->endOfWeek(),
            default      => $current->copy()->endOfMonth()->endOfWeek(),
        };

        $tasks = Task::where('user_id', auth()->id())
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->when($request->get('project_id'), fn($q, $pid) => $q->where('project_id', $pid))
            ->with('project')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status->value,
                'priority' => $t->priority->value,
                'due_date' => $t->due_date->format('Y-m-d'),
                'project_name' => $t->project?->name ?? '',
                'project_color' => $t->project?->color ?? '#8b5cf6',
            ]);

        return Inertia::render('Calendar', [
            'view' => $view,
            'current' => $current->format('Y-m-d'),
            'tasks' => $tasks,
        ]);
    }
}
