<?php

namespace App\Repositories;

use App\Enums\SprintStatus;
use App\Models\Sprint;
use App\Repositories\Interfaces\SprintRepositoryInterface;
use Illuminate\Support\Collection;

class SprintRepository implements SprintRepositoryInterface
{
    // === Get all sprints for a project ===
    public function getByProject(int $projectId): Collection
    {
        return Sprint::where('project_id', $projectId)->latest()->get();
    }

    // === Get the active sprint for a project ===
    public function getActive(int $projectId): ?Sprint
    {
        return Sprint::where('project_id', $projectId)
            ->where('status', SprintStatus::Active)
            ->first();
    }

    // === Create a new sprint ===
    public function create(array $data): Sprint
    {
        return Sprint::create($data);
    }

    // === Update a sprint ===
    public function update(Sprint $sprint, array $data): Sprint
    {
        $sprint->update($data);
        return $sprint->fresh();
    }

    // === Delete a sprint ===
    public function delete(Sprint $sprint): bool
    {
        return $sprint->delete();
    }

    // === Activate a sprint, deactivate others in the project ===
    public function activate(Sprint $sprint): Sprint
    {
        Sprint::where('project_id', $sprint->project_id)
            ->where('status', SprintStatus::Active)
            ->update(['status' => SprintStatus::Completed]);

        $sprint->update(['status' => SprintStatus::Active]);
        return $sprint->fresh();
    }
}
