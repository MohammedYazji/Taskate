<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\Task;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Support\Collection;

class TagRepository implements TagRepositoryInterface
{
    // === get all tags for a user ===
    public function getByUser(int $userId): Collection
    {
        return Tag::where('user_id', $userId)->get();
    }

    // === create a new tag ===
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    // === update tag info ===
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);
        return $tag->fresh();
    }

    // === delete a tag ===
    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    // === add tags to a task ===
    public function attachToTask(Task $task, array $tagIds): void
    {
        $task->tags()->attach($tagIds);
    }

    // === remove a tag from a task ===
    public function detachFromTask(Task $task, int $tagId): void
    {
        $task->tags()->detach($tagId);
    }

    // === replace all tags with enw list (best for update form) ===
    public function syncTaskTags(Task $task, array $tagIds): void
    {
        $task->tags()->sync($tagIds);
    }
}
