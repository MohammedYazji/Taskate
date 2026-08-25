<?php

namespace App\Policies;

use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    // === Any project member can view ===
    public function view(User $user, Sprint $sprint): bool
    {
        return $sprint->project->hasMember($user);
    }

    // === Allow any authenticated user to create (controller gates on the project itself) ===
    public function create(User $user): bool
    {
        return true;
    }

    // === Owner or editor on the sprint's project ===
    public function update(User $user, Sprint $sprint): bool
    {
        return in_array($sprint->project->role($user), ['owner', 'editor'], true);
    }

    // === Owner or editor on the sprint's project ===
    public function delete(User $user, Sprint $sprint): bool
    {
        return in_array($sprint->project->role($user), ['owner', 'editor'], true);
    }

    // === Owner or editor on the sprint's project ===
    public function activate(User $user, Sprint $sprint): bool
    {
        return in_array($sprint->project->role($user), ['owner', 'editor'], true);
    }
}
