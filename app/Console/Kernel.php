<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Assignment;
use App\Models\Suspension;
use App\Models\Notification;
use App\Services\AssignmentService;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function (AssignmentService $assignmentService) {
            //Assignments are deleted 3 days after the deadline
            $assignments = Assignment::whereRaw('DATEDIFF(DATE(now()), DATE(deadline)) >= 3')->get();

            $assignmentService->delete($assignments);

            Suspension::whereRaw('DATEDIFF(DATE(now()), DATE(duration)) >= 1')->delete();

            Notification::whereRaw('DATEDIFF(DATE(now()), DATE(created_at)) >= 3')->delete();
        })->daily();

        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
