<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SmartViewController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected ProjectRepositoryInterface $projectRepository,
        protected TagRepositoryInterface $tagRepository
    ) {}

    private function serializeTask($t)
    {
        return [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'priority' => $t->priority->value,
            'status' => $t->status->value,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
            'project_id' => $t->project_id,
            'project_name' => $t->project?->name ?? '',
            'project_color' => $t->project?->color ?? '#8b5cf6',
            'is_pinned' => $t->is_pinned ?? false,
            'tag_ids' => $t->tags->pluck('id')->toArray(),
            'subtasks' => $t->subtasks->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'is_completed' => $s->is_completed,
            ])->toArray(),
            'comments' => $t->comments->map(fn($c) => [
                'id' => $c->id,
                'body' => $c->body,
                'user_name' => $c->user->name ?? '',
                'created_at' => $c->created_at->diffForHumans(),
            ])->toArray(),
        ];
    }

    private function getUserTags()
    {
        return $this->tagRepository->getByUser(Auth::id())->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'color' => $t->color,
        ]);
    }

    public function today()
    {
        $userId = Auth::id();
        $tasks = $this->taskRepository->getByUser($userId)
            ->filter(fn($t) => $t->status !== \App\Enums\TaskStatus::Done && $t->due_date && $t->due_date->isToday())
            ->values();

        return Inertia::render('Smart/Today', [
            'tasks' => $tasks->map(fn($t) => $this->serializeTask($t)),
            'tags' => $this->getUserTags(),
            'dateRange' => now()->format('l, F j'),
        ]);
    }

    public function next7Days()
    {
        $userId = Auth::id();
        $tasks = $this->taskRepository->getByUser($userId)
            ->filter(fn($t) => $t->status !== \App\Enums\TaskStatus::Done && $t->due_date && $t->due_date->gte(today()) && $t->due_date->lte(now()->addDays(7)))
            ->values();

        return Inertia::render('Smart/Next7Days', [
            'tasks' => $tasks->map(fn($t) => $this->serializeTask($t)),
            'tags' => $this->getUserTags(),
            'dateRange' => now()->format('M j') . ' - ' . now()->addDays(7)->format('M j, Y'),
        ]);
    }

    public function inbox()
    {
        $userId = Auth::id();
        $inboxProject = $this->projectRepository->getByUser($userId)->firstWhere('name', 'Inbox');

        $tasks = $inboxProject
            ? $this->taskRepository->getByUser($userId)
                ->filter(fn($t) => $t->project_id === $inboxProject->id && $t->status !== \App\Enums\TaskStatus::Done)
                ->values()
            : collect();

        return Inertia::render('Smart/Inbox', [
            'tasks' => $tasks->map(fn($t) => $this->serializeTask($t)),
            'tags' => $this->getUserTags(),
        ]);
    }
}
