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

// HR Leave Expiration & Status Automation — daily at 00:01 EAT
Schedule::command('hr:process-leave-expiration')
    ->dailyAt('00:01')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Remind customers to pick up orders ready for 3+ days — daily at 10am EAT
Schedule::command('tasks:send-pickup-reminders')
    ->dailyAt('10:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Remind customers of bag tasks due in 3 days — daily at 8am EAT
Schedule::command('tasks:send-bag-deadline-reminders')
    ->dailyAt('08:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Lead follow-up and promised-order-date reminders — daily at 9:30am EAT
Schedule::command('leads:send-reminders')
    ->dailyAt('09:30')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Remind customers who still owe a balance on super-completed tasks — every 3 days, daily at 8am EAT (saa mbili asubuhi)
Schedule::command('payments:send-balance-reminders')
    ->dailyAt('08:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

// Remind bag (mifuko) customers to reorder 3 days before their bags are projected to run out — daily at 8:30am EAT
Schedule::command('bags:send-reorder-reminders')
    ->dailyAt('08:30')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();
