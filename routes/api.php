<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::apiResource('projects', ProjectController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy']);
Route::patch('projects/{project}', [ProjectController::class, 'patch']);
