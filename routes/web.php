<?php

use App\Http\Controllers\AiTaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EisenhowerController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SmartViewController;
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

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // Smart Views
    Route::get('/today', [SmartViewController::class, 'today'])->name('smart.today');
    Route::get('/next7days', [SmartViewController::class, 'next7Days'])->name('smart.next7days');
    Route::get('/inbox', [SmartViewController::class, 'inbox'])->name('smart.inbox');

    // Pomodoro Timer
    Route::get('/pomodoro', [PomodoroController::class, 'index'])->name('pomodoro.index');
    Route::post('/pomodoro/sessions', [PomodoroController::class, 'store'])->name('pomodoro.sessions.store');
    Route::get('/pomodoro/stats', [PomodoroController::class, 'stats'])->name('pomodoro.stats');

    // Eisenhower Matrix
    Route::get('/eisenhower', [EisenhowerController::class, 'index'])->name('eisenhower.index');
    Route::patch('/tasks/{task}/eisenhower', [EisenhowerController::class, 'move'])->name('eisenhower.move');

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

    Route::patch('/tasks/{task}/description', [TaskController::class, 'updateDescription'])
        ->name('tasks.description');

    Route::resource('tasks', TaskController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // AI Task Generation
    Route::get('/ai/generate', [AiTaskController::class, 'showForm'])->name('ai.form');
    Route::post('/ai/generate', [AiTaskController::class, 'generate'])->name('ai.generate');
    Route::post('/ai/approve', [AiTaskController::class, 'approve'])->name('ai.approve');
});

require __DIR__.'/auth.php';
