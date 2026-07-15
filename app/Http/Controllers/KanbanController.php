<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Interfaces\SprintRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected SprintRepositoryInterface $sprintRepository
    ) {}

    // === Show the kanban board for a project ===
    public function show(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $sprints = $this->sprintRepository->getByProject($project->id);
        $activeSprint = $this->sprintRepository->getActive($project->id);

        $sprintId = $request->get('sprint_id', $activeSprint?->id);

        $tasks = $sprintId
            ? $this->taskRepository->getByProjectAndSprint($project->id, $sprintId)
            : $this->taskRepository->getBacklog($project->id);

        $columns = [
            'todo' => $tasks->where('status', TaskStatus::Todo)->values(),
            'in_progress' => $tasks->where('status', TaskStatus::InProgress)->values(),
            'done' => $tasks->where('status', TaskStatus::Done)->values(),
        ];

        return view('projects.board', compact('project', 'columns', 'sprints', 'sprintId', 'activeSprint'));
    }

    // === AJAX endpoint for drag-and-drop ===
    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'status' => 'required|string|in:todo,in_progress,done',
            'position' => 'required|integer|min:0',
        ]);

        $status = TaskStatus::from($data['status']);
        $this->taskRepository->moveTask($task, $status, $data['position']);

        return response()->json(['success' => true]);
    }
}
