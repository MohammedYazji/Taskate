<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Folder;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Inertia\Inertia;

class TaskController extends Controller
{

    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected TagRepositoryInterface $tagRepository,
        protected ProjectRepositoryInterface $projectRepository
        )
    {}


    // === Get all tasks for the auth user, with all stats ===
    public function dashboard()
    {
        $user = Auth::id();
        $tasks = $this->taskRepository->getByUser($user);
        $totalTasks = $this->taskRepository->countByUser($user);
        $completedTasks = $this->taskRepository->countCompleted($user);
        $overdueTasks = $this->taskRepository->countOverdue($user);
        $tasksDueToday = $this->taskRepository->countDueToday($user);
        $tags = $this->tagRepository->getByUser($user);
        $projects = $this->projectRepository->getWithTaskCounts($user);
        $folders = Folder::where('user_id', $user)
            ->with('projects')
            ->orderBy('pinned', 'desc')
            ->orderBy('position')
            ->get();

        return Inertia::render('Dashboard', [
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                'is_recurring' => $t->is_recurring,
                'project_name' => $t->project?->name ?? 'No project',
                'tag_ids' => $t->tags->pluck('id')->toArray(),
                'project_id' => $t->project_id,
                'assigned_to_id' => $t->assigned_to_id,
                'assigned_to_name' => $t->assignedTo?->name,
                'subtasks' => $t->subtasks->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'is_completed' => $s->is_completed,
                ])->toArray(),
                'comments' => $t->comments->map(fn($c) => [
                    'id' => $c->id,
                    'body' => $c->body,
                    'user_name' => $c->user->name,
                    'created_at' => $c->created_at->diffForHumans(),
                ])->toArray(),
            ])->toArray(),
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'overdueTasks' => $overdueTasks,
            'tasksDueToday' => $tasksDueToday,
            'tags' => $tags,
            'projects' => $projects,
            'folders' => $folders,
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::id();
        $filters = $request->only(['priority', 'status', 'date', 'sort']);
        $tasks = $this->taskRepository->filter($user, $filters);
        $tags = $this->tagRepository->getByUser($user);
        $projects = $this->projectRepository->getByUser($user);

        return Inertia::render('Tasks', [
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                'project_id' => $t->project_id,
                'project_name' => $t->project?->name ?? '',
                'tag_ids' => $t->tags->pluck('id')->toArray(),
                'assigned_to_id' => $t->assigned_to_id,
                'assigned_to_name' => $t->assignedTo?->name,
                'subtasks' => $t->subtasks->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'is_completed' => $s->is_completed,
                ])->toArray(),
                'comments' => $t->comments->map(fn($c) => [
                    'id' => $c->id,
                    'body' => $c->body,
                    'user_name' => $c->user->name ?? '',
                    'created_at' => $c->created_at->diffForHumans(),
                ])->toArray(),
            ]),
            'tags' => $tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color, 'icon' => $t->icon]),
            'projects' => $projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'icon' => $p->icon]),
            'filters' => $filters,
        ]);
    }

    // === Create a new task ===
    public function store(StoreTaskRequest $request)
    {
        $user = Auth::id();
        $data = $request->validated();

        $clean = array_merge($data, ['user_id' => $user]);

        $task = $this->taskRepository->create($clean);

        // Only attach tags the user actually owns
        $userTagIds = $this->tagRepository->getByUser($user)->pluck('id')->toArray();
        $allowedTagIds = array_intersect($request->tag_ids ?? [], $userTagIds);

        if (!empty($allowedTagIds))
        {
            $this->tagRepository->attachToTask($task, $allowedTagIds);
        }

        if ($task->assigned_to_id && $task->assigned_to_id !== $user) {
            $assignee = User::find($task->assigned_to_id);
            $assignee?->notify(new TaskAssignedNotification($task, Auth::user()));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'project_id' => $task->project_id,
                'section_id' => $task->section_id,
                'is_recurring' => $task->is_recurring,
            ]);
        }

        if ($task->project_id) {
            return redirect()->route('projects.show', $task->project_id);
        }

        return redirect()->route('dashboard');
    }

    // === Update a task ===
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        $previousAssigneeId = $task->assigned_to_id;

        // Due date changed: this task becomes eligible for a fresh due-soon reminder
        if (array_key_exists('due_date', $data) && $data['due_date'] != $task->due_date?->format('Y-m-d')) {
            $data['due_reminder_sent_at'] = null;
        }

        $this->taskRepository->update($task, $data);

        if (
            array_key_exists('assigned_to_id', $data)
            && $data['assigned_to_id']
            && $data['assigned_to_id'] !== $previousAssigneeId
            && $data['assigned_to_id'] !== Auth::id()
        ) {
            $assignee = User::find($data['assigned_to_id']);
            $assignee?->notify(new TaskAssignedNotification($task, Auth::user()));
        }

        // Only sync tags the user actually owns
        $userTagIds = $this->tagRepository->getByUser(Auth::id())->pluck('id')->toArray();
        $allowedTagIds = array_intersect($request->tag_ids ?? [], $userTagIds);
        $this->tagRepository->syncTaskTags($task, $allowedTagIds);

        return back();
    }

    // === Toggle task completion ===
    public function toggleComplete(Task $task)
    {
        $this->authorize('toggleStatus', $task);

        $this->taskRepository->toggleComplete($task);

        return redirect()->back();
    }

    // === Remove a task ===
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskRepository->delete($task);

        return back()->with('success', 'Task deleted');
    }

    // === Auto-save description ===
    public function updateDescription(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'description' => $request->input('description', ''),
        ]);

        return back();
    }
}

