<?php

use App\Http\Controllers\AiTaskController;
use App\Http\Controllers\CompletedController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EisenhowerController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SmartViewController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome');
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

    Route::post('/projects/{project}/duplicate', [ProjectController::class, 'duplicate'])
        ->name('projects.duplicate');
    Route::patch('/projects/{project}/pin', [ProjectController::class, 'pin'])
        ->name('projects.pin');

    // Folder Routes
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::patch('/folders/{folder}/pin', [FolderController::class, 'pin'])->name('folders.pin');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // Section Routes
    Route::post('/projects/{project}/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::post('/sections/{section}/above', [SectionController::class, 'storeAbove'])->name('sections.storeAbove');
    Route::post('/sections/{section}/below', [SectionController::class, 'storeBelow'])->name('sections.storeBelow');
    Route::patch('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
    Route::patch('/sections/{section}/move', [SectionController::class, 'move'])->name('sections.move');
    Route::patch('/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');

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
    Route::get('/ai/{generation}', [AiTaskController::class, 'status'])->name('ai.status');
    Route::post('/ai/approve', [AiTaskController::class, 'approve'])->name('ai.approve');

    // Habit Tracker
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::patch('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    Route::patch('/habits/{habit}/toggle', [HabitController::class, 'toggle'])->name('habits.toggle');
    Route::patch('/habits/{habit}/archive', [HabitController::class, 'archive'])->name('habits.archive');
    Route::get('/habits/{habit}/stats', [HabitController::class, 'stats'])->name('habits.stats');

    // Completed
    Route::get('/completed', [CompletedController::class, 'index'])->name('completed.index');

    // Project collaboration: members + invitations
    Route::get('/projects/{project}/members/search', [UserSearchController::class, 'forProject'])->name('projects.members.search');
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])->name('invitations.store');
    Route::post('/invitations/{invitation}/accept', [ProjectInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/decline', [ProjectInvitationController::class, 'decline'])->name('invitations.decline');
    Route::delete('/invitations/{invitation}', [ProjectInvitationController::class, 'cancel'])->name('invitations.cancel');
    Route::delete('/project-members/{member}', [ProjectMemberController::class, 'destroy'])->name('project-members.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

require __DIR__.'/auth.php';
