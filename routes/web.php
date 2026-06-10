<?php

use App\Http\Controllers\ProfileController;
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

    // Tag Resources
    Route::resource('tags', TagController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Task Resources
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle');

    Route::resource('tasks', TaskController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
