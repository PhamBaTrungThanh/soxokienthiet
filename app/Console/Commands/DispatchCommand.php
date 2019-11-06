<?php

/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class DispatchCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "dispatch {job}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Dispatch a job";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = '\\App\\Jobs\\' . $this->argument('job');
        dispatch(new $class());
    }
}
