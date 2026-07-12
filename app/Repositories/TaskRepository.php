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
        return Task::with('project', 'tags', 'subtasks')->where("user_id", $userId)->get();
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

    public function search(int $userId, string $query): Collection
    {
        return Task::with('project', 'tags', 'subtasks')
            ->where('user_id', $userId)
            ->where('title', 'like', "%{$query}%")
            ->get();
    }

    public function filter(int $userId, array $filters): Collection
    {
        $q = Task::with('project', 'tags', 'subtasks')
            ->where('user_id', $userId);

        if (!empty($filters['priority'] ?? null)) {
            $q->where('priority', $filters['priority']);
        }

        if (!empty($filters['status'] ?? null)) {
            if ($filters['status'] === 'done') {
                $q->where('status', \App\Enums\TaskStatus::Done);
            } elseif ($filters['status'] === 'todo') {
                $q->where('status', \App\Enums\TaskStatus::Todo);
            }
        }

        if (!empty($filters['date'] ?? null)) {
            $q->whereDate('due_date', $filters['date']);
        }

        if (!empty($filters['sort'] ?? null)) {
            match ($filters['sort']) {
                'priority' => $q->orderByRaw("FIELD(priority, 'high', 'medium', 'low')"),
                'date_asc'  => $q->orderBy('due_date', 'asc'),
                'date_desc' => $q->orderBy('due_date', 'desc'),
                default     => $q->latest(),
            };
        } else {
            $q->latest();
        }

        return $q->get();
    }
}
