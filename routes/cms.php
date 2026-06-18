<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientPaymentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/', [ClientController::class, 'dashboard'])->name('dashboard');
    });

    // Media Management page — accessible by admin & editor
    Route::get('/media', [MediaController::class, 'page'])->name('media.page');

    Route::prefix('cms')->name('cms.')->group(function () {
        Route::get('/', [SiteContentController::class, 'index'])->name('index');
        Route::post('/seed', [SiteContentController::class, 'seed'])->name('seed');
        Route::get('/{section}', [SiteContentController::class, 'show'])->name('show');
        Route::put('/{section}', [SiteContentController::class, 'update'])->name('update');
        Route::post('/{section}/reset', [SiteContentController::class, 'reset'])->name('reset');

        // Media API — index (list) accessible by admin & editor
        Route::get('/media/index', [MediaController::class, 'index'])->name('media.index');

        // Upload, rename & delete — admin only
        Route::middleware('role:admin')->group(function () {
            Route::post('/media', [MediaController::class, 'store'])->name('media.store');
            Route::put('/media/rename', [MediaController::class, 'rename'])->name('media.rename');
            Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
        });
    });

    // Client Management
    Route::prefix('clients')->name('client.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/{id}', [ClientController::class, 'show'])->name('show');
        Route::put('/{id}/subscription', [ClientController::class, 'updateSubscription'])->name('subscription.update');
        Route::post('/{id}/reset-password', [ClientController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{subscriptionId}/payments', [ClientPaymentController::class, 'store'])->name('payments.store');
    });

    // User Management (admin only)
    Route::middleware('role:admin')->prefix('users')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
});
