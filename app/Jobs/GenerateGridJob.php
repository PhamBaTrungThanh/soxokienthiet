<?php

namespace App\Jobs;

use Illuminate\Support\Str;

class GenerateGridJob extends Job
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $keys = app('redis')->keys(env('LOTTERY_KEY').':*');

        foreach ($keys as $key) {
            $date = Str::after($key, env('LOTTERY_KEY').':');
            dispatch(new GenerateImageGridJob($date));
        }
    }
}
