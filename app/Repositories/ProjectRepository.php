<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Support\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    // === Get all projects for a user ===
    public function getByUser(int $userId): Collection
    {
        return Project::where('user_id', $userId)->get();
    }

    // === Fetch a project by its id ===
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    // === Create a new project ===
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    // === Update a project ===
    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }

    // === Delete a project ===
    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    // === Get projects with completed/total task counts for progress bar ===
    public function getWithTaskCounts(int $userId): Collection
    {
        return Project::where('user_id', $userId)
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', TaskStatus::Done);
            }])
            ->get();
    }
}
