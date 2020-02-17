<?php

namespace App\Jobs\Generates;

use App\Jobs\Job;

class FinishGenerateJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        app('log')->channel('slack')->info($this->message);
    }
}
