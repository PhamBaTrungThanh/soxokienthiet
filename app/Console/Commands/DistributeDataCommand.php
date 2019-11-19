<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Jobs\Distributions\BeginDistributionProcess;
use Illuminate\Console\Command;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class DistributeDataCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'distribute {grid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute a set of grid';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $gridSize = $this->argument('grid');

        if (!$gridSize) {
            $this->warn('No grid size found, guest from environment');

            $gridSize = env('IMAGE_ROW_GRID');
        }

        $imageDirectory = storage_path('images/single');
        $gridDirectory = storage_path("images/grids/{$gridSize}x{$gridSize}");

        $this->info('Loading from directory:');
        $this->info($gridDirectory);
        if (!is_dir($gridDirectory)) {
            $this->error('No directory found for this grid size. Aborting.');

            return false;
        }

        $sourceFilesCount = $this->countFilesInFolder($imageDirectory) - $gridSize * $gridSize;
        $gridFilesCount = $this->countFilesInFolder($gridDirectory);

        if ($gridFilesCount !== $sourceFilesCount) {
            $this->error('Image Grid Generator still running. Abort command');

            return false;
        }

        dispatch(new BeginDistributionProcess($gridDirectory, $gridSize));

        $this->info('Begin distributing data, please wait.');
    }

    private function countFilesInFolder($folder)
    {
        return count(scandir($folder)) - 2;
    }
}
