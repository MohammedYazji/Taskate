<?php

namespace App\Providers;

use App\Repositories\Interfaces\TagRepositoryInterface;
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
