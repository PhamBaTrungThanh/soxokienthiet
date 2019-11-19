<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeValidateDataJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $dataSize;

    public $baseDirectory;

    public function __construct(int $dataSize)
    {
        $this->dataSize = $dataSize;

        $this->baseDirectory = storage_path("app/data/{$this->dataSize}x{$this->dataSize}");
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        for ($i = 99; $i >= 0; --$i) {
            $folder = str_pad($i, 2, '0', STR_PAD_LEFT);
            $this->pickFromFolder($folder);
        }
    }

    public function pickFromFolder(string $folderId)
    {
        $trainFolder = "{$this->baseDirectory}/trains/{$folderId}";
        $validateFolder = "{$this->baseDirectory}/validations/{$folderId}";

        $filesInTrainFolder = count(scandir($trainFolder)) - 2;

        $numberOfFilesToValidate = floor($filesInTrainFolder * config('app.distribution.validation.percent') / 100);

        info('File to validate/Total files: ', [$numberOfFilesToValidate, $filesInTrainFolder]);

        $command = ['shuf', '-n', $numberOfFilesToValidate, '-e', "{$trainFolder}/*", '|', 'xargs', '-i', 'mv', '{}', $validateFolder];

        info(join(' ', $command));
        $process = new Process(join(' ', $command));

        try {
            $process->mustRun();

            info($process->getOutput());
        } catch (ProcessFailedException $exception) {
            throw $exception;
        }
    }
}
