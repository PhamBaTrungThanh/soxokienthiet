<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Console\Traits\ListeningQueueTrait;
use App\Jobs\Generates\GenerateImageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshImageCommand extends Command
{
    use ListeningQueueTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:image {--missing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load library and scan image dir for missing images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Queue::size('generator') > 0) {
            $this->info('Continue generator job');
            $this->listenOnQueue('generator');
        } else {
            $this->startRegenerate();
        }
    }

    public function startRegenerate()
    {
        $keys = app('redis')->keys(config('app.lottery.key').':*');
        $missing = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $exists = $this->checkImageExists($date);
            if (!$exists) {
                $missing[] = $date;
            }
        }
        $this->table(['Total database row', 'Total images'], [
            [count($keys), count($missing)],
        ]);
        if ($this->option('missing')) {
            $jobs = $missing;
            $question = 'Do you want to generate '.count($missing).' missing images?';
        } else {
            $jobs = $keys;
            $question = 'Do you want to regenerate all '.count($keys).' images?';
        }
        if ($this->confirm($question)) {
            $this->info('Make jobs, please wait');
            foreach ($jobs as $date) {
                dispatch(new GenerateImageJob($date))->onQueue('generator');
            }

            $this->listenOnQueue('generator');
        } else {
            $this->info('Command Cancelled');
        }
    }

    private function checkImageExists($date)
    {
        return file_exists(
            storage_path("images/single/{$date}.png")
        );
    }
}
