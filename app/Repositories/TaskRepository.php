<?php

namespace App\Repositories;

use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getByUser(int $userId): Collection
    {
        return Task::where("user_id", $userId)->get();
    }
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }
    public function create(array $data): Task
    {
        return Task::create($data);
    }
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }
    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
