<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KanbanController extends Controller
{
    public function show(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $sections = Section::where('project_id', $project->id)
            ->with(['tasks' => function ($q) {
                $q->where('status', '!=', 'done')->orderBy('position')->with('subtasks');
            }])
            ->orderBy('position')
            ->get();

        $ungroupedTasks = $project->tasks()
            ->whereNull('section_id')
            ->where('status', '!=', 'done')
            ->orderBy('position')
            ->with('subtasks')
            ->get();

        $serializeTask = fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'priority' => $t->priority->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
            'section_id' => $t->section_id,
            'subtasks_total' => $t->subtasks->count(),
            'subtasks_done' => $t->subtasks->where('is_completed', true)->count(),
        ];

        return Inertia::render('Projects/Board', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'icon' => $project->icon,
                'description' => $project->description,
            ],
            'sections' => $sections->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'tasks' => $s->tasks->map($serializeTask)->values(),
            ]),
            'ungroupedTasks' => $ungroupedTasks->map($serializeTask)->values(),
        ]);
    }

    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'section_id' => 'nullable|integer|exists:sections,id',
            'position' => 'required|integer|min:0',
        ]);

        $task->update([
            'section_id' => $data['section_id'] ?? null,
            'position' => $data['position'],
        ]);

        return response()->json(['success' => true]);
    }
}
