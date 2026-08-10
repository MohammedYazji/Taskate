<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TaskRepositoryInterface;

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
        $projects = $this->projectRepository->getByUser($user);

        return view('dashboard', compact(
            'tasks', 'totalTasks', 'completedTasks', 'overdueTasks', 'tasksDueToday', 'tags', 'projects'
        ));
    }

    public function index(Request $request)
    {
        $user = Auth::id();
        $filters = $request->only(['priority', 'status', 'date', 'sort']);
        $tasks = $this->taskRepository->filter($user, $filters);
        $tags = $this->tagRepository->getByUser($user);
        $projects = $this->projectRepository->getByUser($user);

        return view('tasks.index', compact('tasks', 'tags', 'projects', 'filters'));
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

        if ($task->project_id) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json($task);
            }
            return redirect()->route('projects.show', $task->project_id);
        }

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($task);
        }
        return redirect()->route('dashboard');
    }

    // === Update a task ===
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        $this->taskRepository->update($task, $data);

        // Only sync tags the user actually owns
        $userTagIds = $this->tagRepository->getByUser(Auth::id())->pluck('id')->toArray();
        $allowedTagIds = array_intersect($request->tag_ids ?? [], $userTagIds);
        $this->tagRepository->syncTaskTags($task, $allowedTagIds);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($task->fresh(['tags', 'section']));
        }

        return redirect()->route('dashboard');
    }

    // === Toggle task completion ===
    public function toggleComplete(Task $task)
    {
        $this->authorize('update', $task);

        $this->taskRepository->toggleComplete($task);

        return redirect()->back();
    }

    // === Remove a task ===
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskRepository->delete($task);

        return redirect()->route('dashboard');
    }

    // === Auto-save description ===
    public function updateDescription(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'description' => $request->input('description', ''),
        ]);

        return response()->json(['success' => true]);
    }
}

