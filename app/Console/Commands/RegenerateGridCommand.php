<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Jobs\Generates\FinishGenerateJob;
use App\Jobs\Generates\StartGenerateJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Jobs\Generates\GenerateImageGridJob;
use Carbon\Carbon;

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
    protected $signature = 'regenerate:grid';

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
        $keys = app('redis')->keys(config('app.lottery.key') . ':*');
        $queues = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key') . ':');
            $queues[] = new GenerateImageGridJob($date);
        }
        $queues[] = new FinishGenerateJob("Grid generated.\nRun at: " . Carbon::now()->setTimezone('7')->format('Y-m-d H:i'));
        dispatch((new StartGenerateJob('Generate Grid Job'))->chain($queues));
    }
}