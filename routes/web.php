<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])
    ->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/cms/{resource}', [ContentController::class, 'index'])
        ->whereIn('resource', ['profile', 'projects', 'skill-categories', 'skills', 'experience', 'articles', 'messages', 'settings'])
        ->name('cms.index');
    Route::get('/cms/{resource}/create', [ContentController::class, 'create'])
        ->whereIn('resource', ['projects', 'skill-categories', 'skills', 'experience', 'articles'])
        ->name('cms.create');
    Route::post('/cms/{resource}', [ContentController::class, 'store'])
        ->whereIn('resource', ['projects', 'skill-categories', 'skills', 'experience', 'articles'])
        ->name('cms.store');
    Route::get('/cms/{resource}/edit', [ContentController::class, 'editSingleton'])
        ->whereIn('resource', ['profile', 'settings'])
        ->name('cms.edit-singleton');
    Route::put('/cms/{resource}', [ContentController::class, 'updateSingleton'])
        ->whereIn('resource', ['profile', 'settings'])
        ->name('cms.update-singleton');
    Route::get('/cms/{resource}/{record}/edit', [ContentController::class, 'edit'])
        ->whereIn('resource', ['projects', 'skill-categories', 'skills', 'experience', 'articles', 'messages'])
        ->whereNumber('record')
        ->name('cms.edit');
    Route::put('/cms/{resource}/{record}', [ContentController::class, 'update'])
        ->whereIn('resource', ['projects', 'skill-categories', 'skills', 'experience', 'articles', 'messages'])
        ->whereNumber('record')
        ->name('cms.update');
    Route::delete('/cms/{resource}/{record}', [ContentController::class, 'destroy'])
        ->whereIn('resource', ['projects', 'skill-categories', 'skills', 'experience', 'articles', 'messages'])
        ->whereNumber('record')
        ->name('cms.destroy');
});

Route::get('/up', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'OBSIDIAN SIGNAL',
    ]);
});
