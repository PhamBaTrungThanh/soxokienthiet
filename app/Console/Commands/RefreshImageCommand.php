<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Jobs\Generates\GenerateImageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshImageCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:image';

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
        $keys = app('redis')->keys(config('app.lottery.key').':*');
        $images = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $exists = $this->checkImageExists($date);
            if (!$exists) {
                $images[] = $date;
            }
        }
        $this->table(['Total database row', 'Image missing'], [
            [count($keys), count($images)],
        ]);
        $this->info('Start running job');
        foreach ($images as $date) {
            dispatch(new GenerateImageJob($date));
        }
    }

    private function checkImageExists($date)
    {
        return file_exists(
            storage_path("images/single/{$date}.png")
        );
    }
}
