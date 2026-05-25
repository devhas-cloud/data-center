<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated Report Schedules
|--------------------------------------------------------------------------
| Daily   — every day at 07:00, covers previous 24 hours (yesterday)
| Weekly  — every Monday at 08:00, covers last full Mon–Sun week
| Monthly — every 1st of month at 08:00, covers last full calendar month
*/
Schedule::command('reports:send-auto --type=daily')->dailyAt('07:00');
Schedule::command('reports:send-auto --type=weekly')->weeklyOn(1, '08:00');
Schedule::command('reports:send-auto --type=monthly')->monthlyOn(1, '08:00');

/*
|--------------------------------------------------------------------------
| WhatsApp Alert Schedules
|--------------------------------------------------------------------------
| Checks every 2 minutes whether any sensor parameter in tbl_latest_data
| exceeds its alert threshold (tbl_sensor.parameter_indicator_alert).
| If exceeded, sends a WhatsApp reminder to all associated users.
| A cooldown of 1 hour per device+user prevents duplicate messages.
*/
Schedule::command('alerts:send-whatsapp')->everyTwoMinutes();

