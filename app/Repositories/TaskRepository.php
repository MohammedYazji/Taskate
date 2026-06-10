<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    // === Get all the user Tasks ===
    public function getByUser(int $userId): Collection
    {
        return Task::where("user_id", $userId)->get();
    }

    // === Fetch a task via it's id ===
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    // === Create a new task ===
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    // === Update a task ===
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    // === Delete a task ===
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    // === Count of all tasks of a specific user ===
    public function toggleComplete(Task $task): Task
    {
        $task->update([
            'status' => $task->status === TaskStatus::Done ? TaskStatus::Todo : TaskStatus::Done,
        ]);
        return $task->fresh();
    }

    public function countByUser(int $userId): int
    {
        return Task::where('user_id', $userId)->count();
    }

    // === Count of completed tasks ===
    public function countCompleted(int $userId): int
    {
        return Task::where('user_id', $userId)
            ->where('status', TaskStatus::Done)
            ->count();
    }

    // === Count of missing tasks ===
    public function countOverdue(int $userId): int
    {
        return Task::where('user_id', $userId)
            ->where('due_date', '<', today())
            ->where('status', '!=', TaskStatus::Done)
            ->count();
    }

    // === Count of today's tasks ===
    public function countDueToday(int $userId): int
    {
        return Task::where('user_id', $userId)
            ->whereDate('due_date', today())
            ->count();
    }
}
