<?php

namespace App\Providers;

use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\CommentRepository;
use App\Repositories\Interfaces\PomodoroSessionRepositoryInterface;
use App\Repositories\PomodoroSessionRepository;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\SprintRepositoryInterface;
use App\Repositories\Interfaces\SubtaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\SprintRepository;
use App\Repositories\SubtaskRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Repositories\TaskRepository;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\TagRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);

        $this->app->bind(PomodoroSessionRepositoryInterface::class, PomodoroSessionRepository::class);

        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);

        $this->app->bind(SprintRepositoryInterface::class, SprintRepository::class);

        $this->app->bind(SubtaskRepositoryInterface::class, SubtaskRepository::class);

        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);

        $this->app->bind(TagRepositoryInterface::class,
        TagRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.sidebar', function ($view) {
            $userId = Auth::id();
            if (!$userId) return;

            $taskRepo = app(TaskRepositoryInterface::class);
            $projectRepo = app(ProjectRepositoryInterface::class);
            $tagRepo = app(TagRepositoryInterface::class);

            $projects = $projectRepo->getWithTaskCounts($userId);
            $tags = $tagRepo->getByUser($userId);
            $todayCount = $taskRepo->countDueToday($userId);

            $next7Count = $taskRepo->getByUser($userId)
                ->where('status', '!=', 'done')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->count();

            $inboxProject = $projects->firstWhere('name', 'Inbox');
            $inboxCount = $inboxProject ? $inboxProject->tasks_count : 0;

            $totalTasks = $taskRepo->countByUser($userId);
            $completedTasks = $taskRepo->countCompleted($userId);

            $view->with(compact('projects', 'tags', 'todayCount', 'next7Count', 'inboxCount', 'totalTasks', 'completedTasks'));
        });
    }
}
