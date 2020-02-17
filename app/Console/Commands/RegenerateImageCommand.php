<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Jobs\Crawls\VisualizeDataForDate;
use App\Jobs\Generates\FinishGenerateJob;
use App\Jobs\Generates\StartGenerateJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RegenerateImageCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'regenerate:image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rerun image job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keys = app('redis')->keys(config('app.lottery.key').':*');
        $queues = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $queues[] = new VisualizeDataForDate($date);
        }
        $queues[] = new FinishGenerateJob("Image generated.\nRun at: ".Carbon::now()->setTimezone('7')->format('Y-m-d H:i'));
        dispatch((new StartGenerateJob('Regenerate Image Batch'))->chain($queues));
    }
}
