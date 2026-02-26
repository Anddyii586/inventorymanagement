<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Check maintenance schedules daily at 08:00
Schedule::command('maintenance:check-schedules')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->onSuccess(function () {
        info('Maintenance schedules checked successfully');
    })
    ->onFailure(function () {
        info('Maintenance schedules check failed');
    });

