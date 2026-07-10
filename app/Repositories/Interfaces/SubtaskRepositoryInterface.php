<?php

namespace App\Repositories\Interfaces;

use App\Models\Subtask;

interface SubtaskRepositoryInterface
{
    public function create(array $data): Subtask;
    public function update(Subtask $subtask, array $data): Subtask;
    public function delete(Subtask $subtask): bool;
    public function toggleComplete(Subtask $subtask): Subtask;
}
