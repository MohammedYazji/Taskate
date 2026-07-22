<?php

namespace App\Repositories\Interfaces;

use App\Models\PomodoroSession;
use Illuminate\Support\Collection;

interface PomodoroSessionRepositoryInterface
{
    public function create(array $data): PomodoroSession;
    public function complete(PomodoroSession $session): PomodoroSession;
    public function getTodayByUser(int $userId): Collection;
    public function getTodayCountByType(int $userId, string $type): int;
    public function getTodayTotalWorkMinutes(int $userId): int;
}
