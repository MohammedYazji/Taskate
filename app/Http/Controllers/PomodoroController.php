<?php

namespace App\Http\Controllers;

use App\Enums\SessionType;
use App\Models\Task;
use App\Models\Tag;
use App\Repositories\Interfaces\PomodoroSessionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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

        return Inertia::render('Pomodoro', [
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'project_name' => $t->project?->name,
                'tag_ids' => $t->tags->pluck('id')->toArray(),
                'project_id' => $t->project_id,
                'subtasks' => $t->subtasks->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'is_completed' => $s->is_completed,
                ])->toArray(),
                'comments' => $t->comments->map(fn($c) => [
                    'id' => $c->id,
                    'body' => $c->body,
                    'user_name' => $c->user->name,
                    'created_at' => $c->created_at->diffForHumans(),
                ])->toArray(),
            ])->toArray(),
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => ['nullable', Rule::exists('tasks', 'id')->where('user_id', $request->user()->id)],
            'note' => 'nullable|string|max:500',
            'type' => 'required|string',
            'duration' => 'required|integer|min:1',
        ]);

        $this->pomodoroRepo->create([
            'user_id' => $request->user()->id,
            'task_id' => $validated['task_id'] ?? null,
            'note' => $validated['note'] ?? null,
            'type' => $validated['type'],
            'duration' => $validated['duration'],
            'completed' => true,
            'started_at' => now()->subSeconds($validated['duration']),
            'completed_at' => now(),
        ]);

        return back();
    }

    public function stats(Request $request)
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
