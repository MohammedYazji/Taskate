<?php

namespace App\Repositories;

use App\Models\PomodoroSession;
use App\Repositories\Interfaces\PomodoroSessionRepositoryInterface;
use Illuminate\Support\Collection;

class PomodoroSessionRepository implements PomodoroSessionRepositoryInterface
{
    public function create(array $data): PomodoroSession
    {
        return PomodoroSession::create($data);
    }

    public function complete(PomodoroSession $session): PomodoroSession
    {
        $session->update([
            'completed' => true,
            'completed_at' => now(),
        ]);
        return $session->fresh();
    }

    public function getTodayByUser(int $userId): Collection
    {
        return PomodoroSession::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->with('task:id,title')
            ->latest()
            ->get();
    }

    public function getTodayCountByType(int $userId, string $type): int
    {
        return PomodoroSession::where('user_id', $userId)
            ->where('type', $type)
            ->where('completed', true)
            ->whereDate('created_at', today())
            ->count();
    }

    public function getTodayTotalWorkMinutes(int $userId): int
    {
        return PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->where('completed', true)
            ->whereDate('created_at', today())
            ->sum('duration') / 60;
    }
}
