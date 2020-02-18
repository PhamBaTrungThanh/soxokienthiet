<?php

namespace App\Console;

use App\Console\Commands\ClearCommand;
use App\Console\Commands\DispatchCommand;
use App\Console\Commands\DistributeDataCommand;
use App\Console\Commands\RefreshGridCommand;
use App\Console\Commands\RefreshImageCommand;
use App\Console\Commands\TestCommand;
use App\Jobs\Crawls\StartCrawlingDataJob;
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
        DistributeDataCommand::class,
        RefreshImageCommand::class,
        RefreshGridCommand::class,
        TestCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new StartCrawlingDataJob())->daily();
    }
}
