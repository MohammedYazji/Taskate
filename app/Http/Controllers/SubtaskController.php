<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use App\Repositories\Interfaces\SubtaskRepositoryInterface;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function __construct(
        protected SubtaskRepositoryInterface $subtaskRepository
    )
    {}

    // === Create a new subtask under a task ===
    public function store(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $this->subtaskRepository->create(array_merge($data, ['task_id' => $task->id]));

        return redirect()->back();
    }

    // === Update a subtask title ===
    public function update(Request $request, Subtask $subtask)
    {
        $this->authorize('update', $subtask->task);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $this->subtaskRepository->update($subtask, $data);

        return redirect()->back();
    }

    // === Toggle subtask completion ===
    public function toggleComplete(Subtask $subtask)
    {
        $this->authorize('update', $subtask->task);

        $this->subtaskRepository->toggleComplete($subtask);

        return redirect()->back();
    }

    // === Delete a subtask ===
    public function destroy(Subtask $subtask)
    {
        $this->authorize('update', $subtask->task);

        $this->subtaskRepository->delete($subtask);

        return redirect()->back();
    }
}
