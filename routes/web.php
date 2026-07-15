<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [TaskController::class, 'dashboard'])
        ->middleware(['verified'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Subtask Routes
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::patch('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggleComplete'])->name('subtasks.toggle');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');

    // Project Resources
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    // Tag Resources
    Route::resource('tags', TagController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Comments
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Sprint Routes
    Route::resource('projects.sprints', SprintController::class)
        ->shallow()
        ->only(['index', 'store', 'update', 'destroy']);

    Route::patch('/sprints/{sprint}/activate', [SprintController::class, 'activate'])
        ->name('sprints.activate');

    // Kanban Board
    Route::get('/projects/{project}/board', [KanbanController::class, 'show'])
        ->name('projects.board');

    Route::patch('/tasks/{task}/move', [KanbanController::class, 'move'])
        ->name('tasks.move');

    // Task Resources
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle');

    Route::resource('tasks', TaskController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
