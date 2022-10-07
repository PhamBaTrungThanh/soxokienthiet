<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Console\Traits\ListeningQueueTrait;
use App\Jobs\Generates\GenerateImageGridJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshGridCommand extends Command
{
    use ListeningQueueTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:grid {--missing} {--skip-empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load library and scan grid dir for missing images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keys = app('redis')->keys(config('app.lottery.key').':*');
        $missing = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $missing[$date] = $this->checkGridExists($date);
        }
        $this->table(['Total database row', 'Grid missing'], [
            [count($keys), count($missing)],
        ]);

        if ($this->option('missing')) {
            $jobs = $missing;
            $question = 'Do you want to generate '.count($missing).' missing grid?';
        } else {
            $jobs = $keys;
            $question = 'Do you want to regenerate all '.count($keys).' grid?';
        }
        if ($this->confirm($question)) {
            $skipEmpty = $this->option("skip-empty") ? true : false;
            $this->info('Make jobs, please wait.');
            foreach ($jobs as $key) {
                
                dispatch(new GenerateImageGridJob($key, $skipEmpty))->onQueue('generator');
            }

            $this->listenOnQueue('generator');
        } else {
            $this->info('Command Cancelled');
        }
    }

    private function checkGridExists($date)
    {
        $tilePerRow = config('app.image.row_grid');

        return file_exists(
            storage_path("images/grids/{$tilePerRow}x{$tilePerRow}/{$date}.png")
        );
    }
}
