<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\SubscriptionController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/demo', [DemoController::class, 'index'])->name('demo');
Route::post('/demo', [DemoController::class, 'store'])->name('demo.store');

// Static legal pages
Route::get('/legal/privasi', fn () => Inertia::render('Legal', ['type' => 'privacy']))->name('privacy');
Route::get('/legal/syarat', fn () => Inertia::render('Legal', ['type' => 'terms']))->name('terms');
Route::get('/legal/pdp', fn () => Inertia::render('Legal', ['type' => 'pdp']))->name('pdp');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');

// Client Portal (authenticated routes only)
Route::prefix('client')->name('client.')->group(function () {
    Route::middleware(['auth', 'role:client'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
    });
});

// Client Ticket Routes (authenticated clients only)
Route::middleware(['auth'])->prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('/create', [TicketController::class, 'create'])->name('create');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
    Route::put('/{ticket}/close', [TicketController::class, 'close'])->name('close');
});

// Notification API routes
Route::middleware(['auth'])->prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('unread');
    Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
});

// CMS Admin Routes
require __DIR__ . '/cms.php';
