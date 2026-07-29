<?php

use App\Services\NotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-suspend expired KSU tenants after 7-day grace period
Schedule::command('tenant:auto-suspend --grace-days=7')->dailyAt('02:00');

// Clean up read notifications older than 30 days
Schedule::call(function () {
    app(NotificationService::class)->deleteOldRead(30);
})->daily();

// ── Billing Engine ──────────────────────────────────────────
Schedule::command('billing:pre-bill')->dailyAt('06:00');
Schedule::command('billing:dunning')->dailyAt('08:00');
Schedule::command('billing:cancel-expired')->dailyAt('04:00');

// Expire stale payment transactions
Schedule::command('billing:expire-transactions')->everyMinute();
