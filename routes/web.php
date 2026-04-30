<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HemisController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/login/user', [HemisController::class, 'user'])->name('auth.hemis');

Route::prefix('home')->middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/role/{role}', [HomeController::class, 'role']);
    //Route::get('/models', [LanguageController::class, 'models']);
    //Route::post('/upload-scanned-file', [FileController::class, 'uploadScanned'])->name('scan.upload');
    Route::prefix('hemis')->group(function () {
        Route::resource('departments', DepartmentController::class)->only('index');
        Route::resource('curricula', CurriculumController::class)->only('index');
        Route::resource('specialties', SpecialtyController::class)->only('index');
        Route::resource('groups', GroupController::class)->only('index');
    });
    Route::resource('lessons', LessonController::class);
    Route::resource('results', ResultController::class);
    Route::prefix('drive')->name('drive.')->group(function () {
        Route::get('/', [FileController::class, 'years'])->name('index');
        Route::get('/{year}', [FileController::class, 'departments'])->name('departments');
        Route::get('/{year}/{department}', [FileController::class, 'specialties'])->name('specialties');
        Route::get('/{year}/{department}/{specialty}', [FileController::class, 'levels'])->name('levels');
        Route::get('/{year}/{department}/{specialty}/{level}', [FileController::class, 'lessons'])->name('lessons');
        Route::get('/{year}/{department}/{specialty}/{level}/{lesson}', [FileController::class, 'lesson'])->name('lesson');
    });
    Route::resource('accounts', AccountController::class);
    Route::resource('users', UserController::class);
    Route::resource('options', OptionController::class)->only(['index', 'store']);
    Route::get('/event', [AccountController::class, 'event']);
    Route::post('/api/groups/sync-students', [LessonController::class, 'syncStudents'])->name('api.sync_students');
    Route::post('/lessons/{lesson}/start-checking', [LessonController::class, 'startChecking'])->name('lessons.start_checking');
});

Route::get('/certificate/{lesson}/{student}/download', [ResultController::class, 'downloadCertificate'])->name('lessons.certificate');
Route::get('/verify/certificate/{uuid}', [ResultController::class, 'verifyCertificate'])->name('verify.certificate');

Route::get('/statement/{lesson}/download', [ResultController::class, 'downloadStatement'])->name('lessons.statement');
Route::get('/verify/statement/{uuid}', [ResultController::class, 'verifyStatement'])->name('verify.statement');
