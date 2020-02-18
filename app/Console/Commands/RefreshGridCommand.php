<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshGridCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:grid';

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
        $grid = [];
        foreach ($keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $grid[$date] = $this->checkGridExists($date);
        }
        $this->table(['Total database row', 'Grid missing'], [
            [count($keys), count($grid)],
        ]);
    }

    private function checkGridExists($date)
    {
        $tilePerRow = config('app.image.row_grid');

        return file_exists(
            storage_path("images/grids/{$tilePerRow}x{$tilePerRow}/{$date}.png")
        );
    }
}
