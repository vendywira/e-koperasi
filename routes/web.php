<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/demo', [DemoController::class, 'index'])->name('demo');
Route::post('/demo', [DemoController::class, 'store'])->name('demo.store');

// Static legal pages
Route::get('/privacy', fn () => Inertia::render('Legal', ['type' => 'privacy']))->name('privacy');
Route::get('/terms', fn () => Inertia::render('Legal', ['type' => 'terms']))->name('terms');
