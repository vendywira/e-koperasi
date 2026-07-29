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

// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// Google OAuth
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

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

        // Client: Request Tenant
        Route::get('/request-tenant', [\App\Http\Controllers\Client\TenantRequestController::class, 'create'])->name('request-tenant');
        Route::post('/request-tenant', [\App\Http\Controllers\Client\TenantRequestController::class, 'store'])->name('request-tenant.store');
        Route::get('/request-tenant/check-domain', [\App\Http\Controllers\Client\TenantRequestController::class, 'checkDomain'])->name('request-tenant.check-domain');

        // Client: Invoices
        Route::get('/invoices', [\App\Http\Controllers\Client\InvoiceController::class, 'index'])->name('invoices');
        Route::post('/invoices/{id}/upload-proof', [\App\Http\Controllers\Client\InvoiceController::class, 'uploadProof'])->name('invoices.upload-proof');
        Route::get('/invoices/{id}/download', [\App\Http\Controllers\Client\InvoiceController::class, 'download'])->name('invoices.download')->withoutMiddleware([\App\Http\Middleware\HandleInertiaRequests::class]);
        Route::get('/invoices/{id}', [\App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('invoices.show');

        // Client: Coupon validation (AJAX)
        Route::post('/coupon/validate', [\App\Http\Controllers\Client\CouponController::class, 'validate'])->name('coupon.validate');

        // Client: Duitku payment
        Route::post('/payment/duitku', [\App\Http\Controllers\Client\PaymentController::class, 'payViaDuitku'])->name('payment.duitku');

        // Client: Self-hosted payment UI
        Route::get('/invoices/{id}/payment', [\App\Http\Controllers\Client\PaymentController::class, 'showPaymentPage'])->name('invoices.payment');
        Route::post('/payment/initiate', [\App\Http\Controllers\Client\PaymentController::class, 'initiate'])->name('payment.initiate');
        Route::post('/payment/{id}/change-method', [\App\Http\Controllers\Client\PaymentController::class, 'changeMethod'])->name('payment.change-method');
        Route::get('/payment/{id}/status', [\App\Http\Controllers\Client\PaymentController::class, 'status'])->name('payment.status');

        // Mock: simulate Duitku callback (only when DUITKU_MOCK_ENABLED=true)
        Route::post('/payment/simulate-callback', [\App\Http\Controllers\Client\PaymentController::class, 'simulateCallback'])->name('payment.simulate-callback');

        // Client: Subscription upgrade/downgrade
        Route::post('/subscription/upgrade', [\App\Http\Controllers\Client\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
        Route::post('/subscription/change-cycle', [\App\Http\Controllers\Client\SubscriptionController::class, 'changeCycle'])->name('subscription.change-cycle');
        Route::post('/subscription/cancel', [\App\Http\Controllers\Client\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/subscription/resume', [\App\Http\Controllers\Client\SubscriptionController::class, 'resume'])->name('subscription.resume');
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

// Webhook: Duitku (public, no CSRF)
Route::post('/webhook/duitku', \App\Http\Controllers\Webhook\DuitkuController::class)
    ->name('webhook.duitku')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// CMS Admin Routes
require __DIR__ . '/cms.php';
