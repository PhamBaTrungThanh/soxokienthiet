<?php

namespace App\Console;

use App\Console\Commands\ClearCommand;
use App\Console\Commands\DispatchCommand;
use App\Console\Commands\DistributeDataCommand;
use App\Console\Commands\RegenerateGridCommand;
use App\Console\Commands\RegenerateImageCommand;
use App\Jobs\Crawls\StartCrawlerJob;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DispatchCommand::class,
        ClearCommand::class,
        RegenerateGridCommand::class,
        DistributeDataCommand::class,
        RegenerateImageCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new StartCrawlerJob())->daily();
    }
}
