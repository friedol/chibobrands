<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send welcome SMS to new customers — every 30 min during business hours (8am–9pm EAT)
Schedule::command('messages:send-welcome')
    ->everyThirtyMinutes()
    ->between('8:00', '21:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Send overdue follow-up reminders — daily at 9am EAT for leads >3 days overdue
Schedule::command('messages:send-overdue-reminders')
    ->dailyAt('09:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();
