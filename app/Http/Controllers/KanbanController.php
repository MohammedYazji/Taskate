<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
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
                $q->where('status', '!=', 'done')->orderBy('position')->with('subtasks', 'assignedTo');
            }])
            ->orderBy('position')
            ->get();

        $ungroupedTasks = $project->tasks()
            ->whereNull('section_id')
            ->where('status', '!=', 'done')
            ->orderBy('position')
            ->with('subtasks', 'assignedTo')
            ->get();

        $serializeTask = fn($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'priority' => $t->priority->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
            'section_id' => $t->section_id,
            'assigned_to_id' => $t->assigned_to_id,
            'assigned_to_name' => $t->assignedTo?->name,
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
            'currentUserRole' => $project->role(Auth::user()),
            'members' => $this->serializeMembers($project),
            'sections' => $sections->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'tasks' => $s->tasks->map($serializeTask)->values(),
            ]),
            'ungroupedTasks' => $ungroupedTasks->map($serializeTask)->values(),
        ]);
    }

    // === Owner + accepted collaborators, for the members panel ===
    private function serializeMembers(Project $project): array
    {
        $owner = [
            'id' => $project->user->id,
            'member_id' => null,
            'name' => $project->user->name,
            'avatar' => $project->user->avatar,
            'role' => 'owner',
        ];

        $members = $project->members()->get()->map(fn($u) => [
            'id' => $u->id,
            'member_id' => $u->pivot->id,
            'name' => $u->name,
            'avatar' => $u->avatar,
            'role' => $u->pivot->role,
        ])->all();

        return array_merge([$owner], $members);
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

        broadcast(new TaskMoved($task, Auth::id()))->toOthers();

        return response()->json(['success' => true]);
    }
}
