<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

return function (Schedule $schedule) {

    $schedule->command('reminders:class')
        ->dailyAt('09:00')
        ->description('Send class reminders for tomorrow');

    $schedule->command('reminders:assignment')
        ->dailyAt('08:00')
        ->description('Send assignment due reminders');

    $schedule->command('reports:weekly-parents')
        ->sundays()
        ->at('20:00')
        ->description('Send weekly progress reports to parents');

    $schedule->command('reminders:pending-grading')
        ->dailyAt('17:00')
        ->description('Remind instructors about pending grading');

};