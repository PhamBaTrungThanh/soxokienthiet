<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ImageFolderDistributionJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $dataSize;

    public $distributionData;
    public $distributionClass;
    public $distributionType;

    public function __construct(array $distributionData, string $distributionClass, string $distributionType = 'generic')
    {
        $this->distributionData = $distributionData;
        $this->distributionType = $distributionType;
        $this->distributionClass = $distributionClass;
        $this->dataSize = config('app.image.row_grid');

        $this->queue = 'distributor';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $numberOfFilesToValidate = floor(count($this->distributionData) * config('app.distribution.validation.percent') / 100);

        // shuffle data
        $this->secure_shuffle($this->distributionData);
        $filesInTrainFolders = array_slice($this->distributionData, $numberOfFilesToValidate + 1);
        $filesInValidationFolders = array_slice($this->distributionData, 0, $numberOfFilesToValidate);
        $this->runCopyCommand($filesInTrainFolders, 'train');
        $this->runCopyCommand($filesInValidationFolders, 'validate');
    }

    /**
     * Shuffle an array using a CSPRNG.
     *
     * @see https://paragonie.com/b/JvICXzh_jhLyt4y3
     *
     * @param &array $array reference to an array
     */
    public function secure_shuffle(&$array)
    {
        $size = count($array);
        $keys = array_keys($array);
        for ($i = $size - 1; $i > 0; --$i) {
            $r = random_int(0, $i);
            if ($r !== $i) {
                $temp = $array[$keys[$r]];
                $array[$keys[$r]] = $array[$keys[$i]];
                $array[$keys[$i]] = $temp;
            }
        }
        // Reset indices:
        $array = array_values($array);
    }

    public function makeCopyCommand(array $dates, string $folderType)
    {
        $paths = [];
        foreach ($dates as $date) {
            $path = storage_path("images/grids/{$this->dataSize}x{$this->dataSize}/{$date}.png");
            if (!file_exists($path)) {
                continue;
            }
            $paths[] = storage_path("images/grids/{$this->dataSize}x{$this->dataSize}/{$date}.png");
        }
        $paths[] = Str::finish(config('app.distribution.path'), '/')."{$this->dataSize}x{$this->dataSize}/{$this->distributionType}/{$folderType}/{$this->distributionClass}";

        return $paths;
    }

    public function runCopyCommand($folder, $folderType)
    {
        $bits = $this->makeCopyCommand($folder, $folderType);
        if (1 === count($bits)) {
            return false;
        }
        $destination = end($bits);
        if (!is_dir($destination)) {
            mkdir($destination, '0777', true);
        } else {
            array_map('unlink', array_filter((array) glob($destination.'/*')));
        }

        $command = 'cp '.implode(' ', $bits);
        $process = new Process($command);

        try {
            $process->mustRun();

            info($process->getOutput());
        } catch (ProcessFailedException $exception) {
            throw $exception;
        }
    }
}
