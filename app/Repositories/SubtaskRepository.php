<?php

namespace App\Repositories;

use App\Models\Subtask;
use App\Repositories\Interfaces\SubtaskRepositoryInterface;

class SubtaskRepository implements SubtaskRepositoryInterface
{
    // === Create a new subtask ===
    public function create(array $data): Subtask
    {
        return Subtask::create($data);
    }

    // === Update a subtask ===
    public function update(Subtask $subtask, array $data): Subtask
    {
        $subtask->update($data);
        return $subtask->fresh();
    }

    // === Delete a subtask ===
    public function delete(Subtask $subtask): bool
    {
        return $subtask->delete();
    }

    // === Toggle subtask completion status ===
    public function toggleComplete(Subtask $subtask): Subtask
    {
        $subtask->update([
            'is_completed' => !$subtask->is_completed,
        ]);
        return $subtask->fresh();
    }
}
