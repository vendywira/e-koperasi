<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteContentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/cms')->name('admin.cms.')->group(function () {
    Route::get('/', [SiteContentController::class, 'index'])->name('index');
    Route::post('/seed', [SiteContentController::class, 'seed'])->name('seed');
    Route::get('/{section}', [SiteContentController::class, 'show'])->name('show');
    Route::put('/{section}', [SiteContentController::class, 'update'])->name('update');
    Route::post('/{section}/reset', [SiteContentController::class, 'reset'])->name('reset');

    // Media
    Route::get('/media/index', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
});
