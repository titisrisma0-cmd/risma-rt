<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CheckIuranDue::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // jalankan sekali sehari
        $schedule->command('iuran:check-due')->daily();
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
