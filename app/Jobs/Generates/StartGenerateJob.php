<?php

namespace App\Jobs\Generates;

use App\Jobs\Job;

class StartGenerateJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $jobName;

    public function __construct(string $jobName)
    {
        $this->jobName = $jobName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        info('::run:: '.$this->jobName);
    }
}
