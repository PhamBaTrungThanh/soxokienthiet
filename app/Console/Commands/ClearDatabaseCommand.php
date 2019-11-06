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
class ClearDatabaseCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "clear:database";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clear redis database";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app('redis')->flushAll();
    }
}
