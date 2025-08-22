<?php

use App\Jobs\CalculateDailyStats;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CalculateDailyStats())->daily()->at('00:30')->name('calculate-daily-stats');
Schedule::command('stats:calculate-daily')->daily()->at('01:00')->name('manual-stats-backup');
