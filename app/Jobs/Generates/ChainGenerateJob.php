<?php

namespace App\Jobs\Generates;

use App\Jobs\Job;

class ChainGenerateJob extends Job
{
    public $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Chain generate image and grid together
        info('StartGenerateImageJob::run::make_image_for_date,'.$this->date);
        dispatch(new GenerateImageJob($this->date, $this->map))->chain([
            new GenerateImageGridJob($this->date),
        ]);
    }
}
