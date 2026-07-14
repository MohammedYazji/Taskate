<?php

namespace App\Policies;

use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    // === Verify user owns the sprint's project ===
    public function view(User $user, Sprint $sprint): bool
    {
        return $user->id === $sprint->project->user_id;
    }

    // === Allow any authenticated user to create ===
    public function create(User $user): bool
    {
        return true;
    }

    // === Verify user owns the sprint's project ===
    public function update(User $user, Sprint $sprint): bool
    {
        return $user->id === $sprint->project->user_id;
    }

    // === Verify user owns the sprint's project ===
    public function delete(User $user, Sprint $sprint): bool
    {
        return $user->id === $sprint->project->user_id;
    }

    // === Verify user owns the sprint's project ===
    public function activate(User $user, Sprint $sprint): bool
    {
        return $user->id === $sprint->project->user_id;
    }
}
