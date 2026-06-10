<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Validation\Rules\Enum;

class TaskController extends Controller
{

    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected TagRepositoryInterface $tagRepository
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

        return view('dashboard', compact(
            'tasks', 'totalTasks', 'completedTasks', 'overdueTasks', 'tasksDueToday', 'tags'
        ));
    }

    public function index()
    {
        $user = Auth::id();
        $tasks = $this->taskRepository->getByUser($user);
        $tags = $this->tagRepository->getByUser($user);

        return view('tasks.index', compact('tasks', 'tags'));
    }

    // === Create a new task ===
    public function store(Request $request)
    {
        $user = Auth::id();
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'is_recurring'=> 'boolean|nullable',
            'priority' => [new Enum(Priority::class)],
            'status' => [new Enum(TaskStatus::class)],
            'due_date' => 'nullable|date'
        ]);

        $clean = array_merge($data, ['user_id' => $user]);

        $task = $this->taskRepository->create($clean);

        if ($request->has('tag_ids'))
        {
            $this->tagRepository->attachToTask($task, $request->tag_ids);
        }

        return redirect()->route('dashboard');
    }

    // === Update a task ===
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'is_recurring'=> 'boolean|nullable',
            'priority' => [new Enum(Priority::class)],
            'status' => [new Enum(TaskStatus::class)],
            'due_date' => 'nullable|date'
        ]);

        $this->taskRepository->update($task, $data);

        // sync tags (replaces old selection with new)
        $this->tagRepository->syncTaskTags($task, $request->tag_ids ?? []);

        return redirect()->route('dashboard');
    }

    // === Remove a task ===
    public function toggleComplete(Task $task)
    {
        $this->taskRepository->toggleComplete($task);

        return redirect()->back();
    }

    public function destroy(Task $task)
    {
        $this->taskRepository->delete($task);

        return redirect()->route('dashboard');
    }
}

