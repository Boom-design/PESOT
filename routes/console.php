<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Job Fair — daily check para i-send ang attendance confirmation sa mga jobseeker nga naay event karong adlawa ──
Schedule::command('jobfair:send-attendance-confirmations')->dailyAt('06:00');

// ── In-house — daily check para i-send ang participation reminder 5 days sa dili pa ang schedule ──
Schedule::command('inhouse:send-participation-reminders')->dailyAt('06:30');

// ── Job Fair — daily auto-update sa event status (upcoming → ongoing → completed) base sa event_date ──
Schedule::command('jobfair:update-event-statuses')->dailyAt('00:05');
    