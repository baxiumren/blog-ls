<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

// Heartbeat — proves the scheduler is alive (runs every minute the cron fires)
Schedule::call(function () {
    Cache::put('cron.heartbeat', now()->toDateTimeString());
})->everyMinute()->name('cron-heartbeat')->withoutOverlapping();

Schedule::command('sync:fixtures')
    ->hourly()
    ->withoutOverlapping()
    ->after(fn () => Cache::put('cron.sync_fixtures', now()->toDateTimeString()));

Schedule::command('sync:today')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->after(fn () => Cache::put('cron.sync_today', now()->toDateTimeString()));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');