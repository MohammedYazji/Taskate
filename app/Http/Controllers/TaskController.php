<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Validation\Rules\Enum;

class TaskController extends Controller
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }


    // === Get all tasks for the auth user, with all stats ===
    public function dashboard()
    {
        $user = Auth::id();
        $tasks = $this->taskRepository->getByUser($user);
        $totalTasks = $this->taskRepository->countByUser($user);
        $completedTasks = $this->taskRepository->countCompleted($user);
        $overdueTasks = $this->taskRepository->countOverdue($user);
        $tasksDueToday = $this->taskRepository->countDueToday($user);

        return view('dashboard', compact(
            'tasks', 'totalTasks', 'completedTasks', 'overdueTasks', 'tasksDueToday'
        ));
    }

    public function index()
    {
        $user = Auth::id();
        $tasks = $this->taskRepository->getByUser($user);

        return view('tasks.index', compact('tasks'));
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

        $this->taskRepository->create($clean);

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

