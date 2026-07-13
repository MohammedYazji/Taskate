<?php

namespace App\Providers;

use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\CommentRepository;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\SubtaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\SubtaskRepository;
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

        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);

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
        //
    }
}
