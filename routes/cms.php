<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteContentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
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
});
