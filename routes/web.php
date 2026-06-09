<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function (){

    // Tasks Resources
    Route::resource('tasks',TaskController::class)->only(['index', 'store', 'update', 'destroy']);
});
