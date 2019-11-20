<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Jobs\Generates\GenerateGridJob;
use Illuminate\Console\Command;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RegenerateGridCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'grid:regenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rerun grid job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dispatch(new GenerateGridJob());
    }
}
