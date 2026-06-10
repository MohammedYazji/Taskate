<?php

namespace App\Repositories\Interfaces;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Support\Collection;

interface TagRepositoryInterface
{
    public function getByUser(int $userId): Collection;
    public function create(array $data): Tag;
    public function update(Tag $tag, array $data): Tag;
    public function delete(Tag $tag): bool;
    public function attachToTask(Task $task, array $tagIds): void;
    public function detachFromTask(Task $task, int $tagId): void;
    public function syncTaskTags(Task $task, array $tagIds): void;
}
