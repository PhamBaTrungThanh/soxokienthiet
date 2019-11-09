<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;

class PrepareDataStorageJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $dataSize;

    public function __construct(int $dataSize)
    {
        $this->dataSize = $dataSize;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $baseDirectory = storage_path("app/data/{$this->dataSize}x{$this->dataSize}");
        if (is_dir($baseDirectory)) {
            $this->recursiveRemove($baseDirectory);
        }
        mkdir($baseDirectory, '0777', true);
        mkdir("{$baseDirectory}/trains");
        mkdir("{$baseDirectory}/validations");

        for ($i = 99; $i >= 0; --$i) {
            $classDirName = str_pad($i, 2, '0', STR_PAD_LEFT);
            mkdir("{$baseDirectory}/trains/{$classDirName}");
            mkdir("{$baseDirectory}/validations/{$classDirName}");
        }
    }

    /**
     * Recursive remove directory and files.
     *
     * @param mixed $dir
     */
    public function recursiveRemove($dir)
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->recursiveRemove("{$file}/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}
