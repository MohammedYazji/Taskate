<?php

namespace App\Repositories\Interfaces;

use App\Models\Sprint;
use Illuminate\Support\Collection;

interface SprintRepositoryInterface
{
    public function getByProject(int $projectId): Collection;
    public function getActive(int $projectId): ?Sprint;
    public function create(array $data): Sprint;
    public function update(Sprint $sprint, array $data): Sprint;
    public function delete(Sprint $sprint): bool;
    public function activate(Sprint $sprint): Sprint;
}
