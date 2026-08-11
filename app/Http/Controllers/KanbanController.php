<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function show(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $sections = Section::where('project_id', $project->id)
            ->with(['tasks' => function ($q) {
                $q->where('status', '!=', 'done')->orderBy('position');
            }])
            ->orderBy('position')
            ->get();

        $ungroupedTasks = $project->tasks()
            ->whereNull('section_id')
            ->where('status', '!=', 'done')
            ->orderBy('position')
            ->get();

        return view('projects.board', compact('project', 'sections', 'ungroupedTasks'));
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
