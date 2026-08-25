<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        if ($user->id === $task->user_id || $user->id === $task->assigned_to_id) {
            return true;
        }

        return $task->project?->hasMember($user) ?? false;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->id === $task->user_id) {
            return true;
        }

        return $task->project ? in_array($task->project->role($user), ['owner', 'editor'], true) : false;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    // Assignees may toggle status and comment even with a viewer role
    public function toggleStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task) || $user->id === $task->assigned_to_id;
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->update($user, $task) || $user->id === $task->assigned_to_id;
    }
}
