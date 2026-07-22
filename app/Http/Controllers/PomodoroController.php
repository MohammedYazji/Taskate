<?php

namespace App\Http\Controllers;

use App\Enums\SessionType;
use App\Models\Task;
use App\Models\Tag;
use App\Repositories\Interfaces\PomodoroSessionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function __construct(
        private PomodoroSessionRepositoryInterface $pomodoroRepo,
    ) {}

    public function index(Request $request)
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->where('status', '!=', 'done')
            ->with('project:id,name,color', 'tags:id,name,color', 'subtasks:id,task_id,title,is_completed', 'comments.user:id,name')
            ->orderBy('title')
            ->get();

        $tags = Tag::where('user_id', $request->user()->id)->get();

        return view('pomodoro.index', compact('tasks', 'tags'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'note' => 'nullable|string|max:500',
            'type' => 'required|string',
            'duration' => 'required|integer|min:1',
        ]);

        $session = $this->pomodoroRepo->create([
            'user_id' => $request->user()->id,
            'task_id' => $validated['task_id'] ?? null,
            'note' => $validated['note'] ?? null,
            'type' => $validated['type'],
            'duration' => $validated['duration'],
            'completed' => true,
            'started_at' => now()->subSeconds($validated['duration']),
            'completed_at' => now(),
        ]);

        return response()->json($session);
    }

    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'work_count' => $this->pomodoroRepo->getTodayCountByType($userId, SessionType::Work->value),
            'short_break_count' => $this->pomodoroRepo->getTodayCountByType($userId, SessionType::ShortBreak->value),
            'long_break_count' => $this->pomodoroRepo->getTodayCountByType($userId, SessionType::LongBreak->value),
            'total_minutes' => $this->pomodoroRepo->getTodayTotalWorkMinutes($userId),
            'sessions' => $this->pomodoroRepo->getTodayByUser($userId),
        ]);
    }
}
