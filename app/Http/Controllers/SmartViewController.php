<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartViewController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected ProjectRepositoryInterface $projectRepository,
        protected TagRepositoryInterface $tagRepository
    ) {}

    public function today()
    {
        $userId = Auth::id();
        $tasks = $this->taskRepository->getByUser($userId)
            ->where('status', '!=', 'done')
            ->whereDate('due_date', today())
            ->values();

        $tags = $this->tagRepository->getByUser($userId);
        $projects = $this->projectRepository->getWithTaskCounts($userId);

        return view('smart.today', compact('tasks', 'tags', 'projects'));
    }

    public function next7Days()
    {
        $userId = Auth::id();
        $tasks = $this->taskRepository->getByUser($userId)
            ->where('status', '!=', 'done')
            ->whereBetween('due_date', [today()->toDateString(), now()->addDays(7)->toDateString()])
            ->values();

        $tags = $this->tagRepository->getByUser($userId);
        $projects = $this->projectRepository->getWithTaskCounts($userId);

        return view('smart.next7days', compact('tasks', 'tags', 'projects'));
    }

    public function inbox()
    {
        $userId = Auth::id();
        $inboxProject = $this->projectRepository->getByUser($userId)->firstWhere('name', 'Inbox');

        $tasks = $inboxProject
            ? $this->taskRepository->getByUser($userId)
                ->where('project_id', $inboxProject->id)
                ->where('status', '!=', 'done')
                ->values()
            : collect();

        $tags = $this->tagRepository->getByUser($userId);
        $projects = $this->projectRepository->getWithTaskCounts($userId);

        return view('smart.inbox', compact('tasks', 'tags', 'projects'));
    }
}
