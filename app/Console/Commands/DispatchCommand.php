<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class DispatchCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'dispatch {job} {--queue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = '\\App\\Jobs\\'.$this->argument('job');

        dispatch(new $class())->onQueue(filled($this->option('queue')) ? $this->option('queue') : 'default');
    }
}
