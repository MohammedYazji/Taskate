<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    public function update(User $user, Project $project): bool
    {
        return in_array($project->role($user), ['owner', 'editor'], true);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
