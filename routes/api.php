<?php

use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('subjects', SubjectController::class)->only(['index', 'store']);

Route::post('subjects/{subject}/tasks', [TaskController::class, 'store']);
Route::post('tasks/{task}/items', [TaskController::class, 'addItem']);
Route::patch('task-items/{taskItem}/toggle', [TaskController::class, 'toggle']);
