<?php

use App\Http\Controllers\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/departments/{department}/specialties', [LessonController::class, 'getSpecialties']);
Route::get('/specialties/{specialty}/groups', [LessonController::class, 'getGroups']);
