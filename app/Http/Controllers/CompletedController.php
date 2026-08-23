<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CompletedController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected TagRepositoryInterface $tagRepository,
        protected ProjectRepositoryInterface $projectRepository
    ) {}

    public function index(Request $request)
    {
        $userId = Auth::id();
        $dateFilter = $request->get('date', 'all');
        $projectFilter = $request->get('project', 'all');

        $tasks = $this->taskRepository->getByUser($userId)
            ->filter(fn($t) => in_array($t->status->value, ['done', 'wont_do']));

        if ($dateFilter !== 'all') {
            $tasks = $tasks->filter(function ($t) use ($dateFilter) {
                if (!$t->updated_at) return false;
                return match ($dateFilter) {
                    'week' => $t->updated_at->isCurrentWeek(),
                    'last_week' => $t->updated_at->isLastWeek(),
                    'month' => $t->updated_at->isCurrentMonth(),
                    default => true,
                };
            });
        }

        if ($projectFilter !== 'all') {
            if ($projectFilter === 'inbox') {
                $inboxProject = $this->projectRepository->getByUser($userId)->firstWhere('name', 'Inbox');
                $tasks = $tasks->filter(fn($t) => $inboxProject && $t->project_id === $inboxProject->id);
            } else {
                $tasks = $tasks->filter(fn($t) => (string) $t->project_id === $projectFilter);
            }
        }

        $tasks = $tasks->sortByDesc('updated_at')->values();

        $projects = $this->projectRepository->getWithTaskCounts($userId);
        $tags = $this->tagRepository->getByUser($userId);

        return \Inertia\Inertia::render('Completed', [
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
                'project_name' => $t->project?->name ?? '',
            ]),
            'projects' => $projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'icon' => $p->icon ?? '']),
            'dateFilter' => $dateFilter,
            'projectFilter' => $projectFilter,
        ]);
    }
}
