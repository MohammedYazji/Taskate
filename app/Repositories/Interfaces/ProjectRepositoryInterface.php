<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use Illuminate\Support\Collection;

interface ProjectRepositoryInterface
{
    public function getByUser(int $userId): Collection;
    public function findById(int $id): ?Project;
    public function create(array $data): Project;
    public function update(Project $project, array $data): Project;
    public function delete(Project $project): bool;
    public function getWithTaskCounts(int $userId): Collection;
}
