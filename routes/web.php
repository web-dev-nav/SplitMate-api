<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LegacyImportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [DashboardController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [DashboardController::class, 'editUser'])->name('users.edit');
        Route::post('/users/{user}/edit', [DashboardController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/toggle', [DashboardController::class, 'toggleUser'])->name('users.toggle');
        Route::post('/users/{user}/delete', [DashboardController::class, 'deleteUser'])->name('users.delete');
        Route::get('/groups', [DashboardController::class, 'groups'])->name('groups');
        Route::post('/groups/{group}/delete', [DashboardController::class, 'deleteGroup'])->name('groups.delete');
        Route::get('/groups/{group}', [DashboardController::class, 'showGroup'])->name('groups.show');
        Route::get('/api-access', [DashboardController::class, 'apiDocs'])->name('api-docs');
        Route::match(['get', 'post'], '/tools/legacy-import', LegacyImportController::class)
            ->name('tools.legacy-import');
    });
});
