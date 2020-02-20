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
class ClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'clear {--A|all} {--D|database} {--I|image} {--G|grid} {--T|distribution}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();
        if (0 === count(array_filter($this->options()))) {
            $this->info('Nothing specified, ending');
        } else {
            $allSwitch = $this->option('all');
            $databaseSwitch = $this->option('database');
            $imageSwitch = $this->option('image');
            $gridSwitch = $this->option('grid');
            $distributionSwitch = $this->option('distribution');
            if ($allSwitch) {
                $question = 'Do you want to clear everything? (Including database, images, grid, distributions)';
            } else {
                $options = array_filter([$databaseSwitch ? 'database' : '', $imageSwitch ? 'image' : '', $gridSwitch ? 'grid' : '', $distributionSwitch ? 'distribution' : '']);
                $question = 'Do you want to clear '.implode(', ', $options).'?';
            }
            if ($this->confirm($question)) {
                $this->info('Begin cleaning');
                $bar = $this->output->createProgressBar($allSwitch ? 4 : count($options));

                $bar->start();

                if ($allSwitch || $databaseSwitch) {
                    $this->clearDatabase();
                    $bar->advance();
                }
                if ($allSwitch || $imageSwitch) {
                    $this->clearImages();
                    $bar->advance();
                }
                if ($allSwitch || $gridSwitch) {
                    $this->clearGrids();
                    $bar->advance();
                }

                if ($allSwitch || $distributionSwitch) {
                    $this->clearDistributions();
                    $bar->advance();
                }

                $bar->finish();
            }

            $this->info("\nCleaned!");
        }
    }

    public function clearDatabase()
    {
        app('redis')->flushAll();
    }

    public function clearImages()
    {
        $this->deleteDir(storage_path('images/single'), false);
    }

    public function clearGrids()
    {
        $directories = $this->scanDir(storage_path('images/grids'));

        foreach ($directories as $dir) {
            $this->deleteDir(storage_path("images/grids/{$dir}"));
        }
    }

    public function clearDistributions()
    {
        // $directories = $this->scanDir(('images/single'));
        // foreach ($directories as $dir) {
        //     $this->deleteDir($dir);
        // }
    }

    private function scanDir($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }

        return  array_diff(scandir($dir), ['..', '.']);
    }

    private function deleteDir($target, $removeDir = true)
    {
        if (is_dir($target)) {
            $files = glob($target.'*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned
            foreach ($files as $file) {
                $this->deleteDir($file, $removeDir);
            }
            if (is_dir($target)) {
                if ($removeDir) {
                    rmdir($target);
                }
            }
        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
