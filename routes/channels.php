<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return Project::find($projectId)?->hasMember($user) ?? false;
});

Broadcast::channel('online', function ($user) {
    return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar ?? null];
});

Broadcast::channel('project.presence.{projectId}', function ($user, $projectId) {
    $project = Project::find($projectId);
    if (!$project || !$project->hasMember($user)) {
        return false;
    }
    return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar ?? null];
});
