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
