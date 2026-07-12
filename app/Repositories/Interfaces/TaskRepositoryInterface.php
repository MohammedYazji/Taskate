<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function getByUser(int $userId): Collection;
    public function findById(int $id): ?Task;
    public function create(array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): bool;
    public function toggleComplete(Task $task): Task;
    public function countByUser(int $userId): int;
    public function countCompleted(int $userId): int;
    public function countOverdue(int $userId): int;
    public function countDueToday(int $userId): int;
    public function search(int $userId, string $query): Collection;
    public function filter(int $userId, array $filters): Collection;
}
