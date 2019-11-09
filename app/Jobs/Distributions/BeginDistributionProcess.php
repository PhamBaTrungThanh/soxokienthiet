<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BeginDistributionProcess extends Job
{
    /**
     * Create a new job instance.
     */
    public $dataSize;
    public $directory;

    public function __construct(string $directory, int $dataSize)
    {
        $this->dataSize = $dataSize;
        $this->directory = Str::finish($directory, '/');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $chains = [];

        $count = 0;
        foreach (glob($this->directory.'*.png') as $file) {
            $fileId = Str::after(Str::before($file, '.png'), $this->directory);

            $chains[] = new ImageClassificationJob($fileId, $this->dataSize);
            ++$count;
        }
        info("Create chained job for: {$count}");
        $chains[] = new MakeValidateDataJob($this->dataSize);
        $chains[] = new DistributionCompletedNotifyJob($this->dataSize, Carbon::now());
        dispatch((new PrepareDataStorageJob($this->dataSize))->chain($chains));
    }
}
