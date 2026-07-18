<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientPaymentController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Root redirect — admin goes to dashboard, editor goes to CMS, it-ops goes to tenant-requests
    Route::get('/', function () {
        $user = request()->user();
        return match ($user->role) {
            'admin' => app(\App\Http\Controllers\Admin\ClientController::class)->dashboard(),
            'editor' => redirect()->route('admin.cms.index'),
            'it-ops' => redirect()->route('admin.tenant-request.index'),
            default => redirect()->route('admin.ticket.index'),
        };
    })->name('dashboard');

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

    // Tenant Overview (admin & it-ops) — gabung request + tenant + invoice
    Route::middleware('role:admin,it-ops')->prefix('tenant-overview')->name('tenant-overview.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TenantOverviewController::class, 'index'])->name('index');
    });

    // Tenant Requests (admin & it-ops)
    Route::middleware('role:admin,it-ops')->prefix('tenant-requests')->name('tenant-request.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TenantRequestController::class, 'index'])->name('index');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\TenantRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\TenantRequestController::class, 'reject'])->name('reject');
    });

    // Invoice management (admin & it-ops)
    Route::middleware('role:admin,it-ops')->prefix('invoices')->name('invoice.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('index');
        Route::post('/{id}/generate', [\App\Http\Controllers\Admin\InvoiceController::class, 'generate'])->name('generate');
        Route::post('/{id}/confirm-paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'confirmPaid'])->name('confirm-paid');
    });

    // KSU Tenant Management (admin only — billing/subscription sensitive)
    Route::middleware('role:admin')->prefix('tenants')->name('tenant.')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::get('/check-domain', [TenantController::class, 'checkDomain'])->name('check-domain');
        Route::get('/{id}', [TenantController::class, 'show'])->name('show');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::put('/{id}', [TenantController::class, 'update'])->name('update');
        Route::post('/{id}/extend', [TenantController::class, 'extend'])->name('extend');
        Route::post('/{id}/toggle-suspend', [TenantController::class, 'toggleSuspend'])->name('toggle-suspend');
        Route::delete('/{id}', [TenantController::class, 'destroy'])->name('destroy');
    });

    // Ticket Management (admin & it-ops)
    Route::middleware('role:admin,it-ops')->prefix('tickets')->name('ticket.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
        Route::get('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
        Route::put('/{ticket}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
        Route::put('/{ticket}/assign', [\App\Http\Controllers\Admin\TicketController::class, 'assign'])->name('assign');
        Route::delete('/{ticket}/attachment/{attachment}', [\App\Http\Controllers\Admin\TicketController::class, 'destroyAttachment'])->name('attachment.destroy');
    });

    // Notifications page
    Route::middleware('role:admin,it-ops')->get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
});
