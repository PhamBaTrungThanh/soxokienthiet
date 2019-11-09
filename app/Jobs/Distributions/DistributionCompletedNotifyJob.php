<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Carbon\Carbon;

class DistributionCompletedNotifyJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $dataSize;

    public $runAt;

    public function __construct(int $dataSize, Carbon $runAt)
    {
        $this->dataSize = $dataSize;

        $this->runAt = $runAt;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        app('log')->channel('slack')->info("Data distribution for Grid size {$this->dataSize}x{$this->dataSize} completed \nRun at: {$this->runAt->setTimezone('7')->format('Y-m-d H:i')}");
    }
}
