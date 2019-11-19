<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ImageClassificationJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $fileId;

    public $date;

    public $dataSize;

    public function __construct(string $fileId, int $dataSize)
    {
        $this->fileId = $fileId;

        $this->date = Carbon::parse($fileId);

        $this->dataSize = $dataSize;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        info('Image for: ', [$this->date->format('Y-m-d')]);
        $key = sprintf('%s:%s', env('LOTTERY_KEY'), $this->date->clone()->addDay()->format('Y-m-d'));

        $result = app('redis')->get($key);

        if (!$result) {
            app('log')->warning('Data for next day not found. Skip this file.');

            return $this->delete();
        }

        $baseDirectory = storage_path("app/data/{$this->dataSize}x{$this->dataSize}/trains");

        $originalGrid = storage_path("images/grids/{$this->dataSize}x{$this->dataSize}/{$this->fileId}.png");

        $classes = $this->flatten($result);

        foreach ($classes as $class) {
            $copyPath = "{$baseDirectory}/{$class}/{$this->fileId}.png";
            if (file_exists($copyPath)) {
                unlink($copyPath);
            }
            copy($originalGrid, $copyPath);
        }
    }

    /**
     * Flatten data.
     *
     * @return array
     */
    public function flatten(string $result)
    {
        $data = json_decode($result);

        $flatten = collect(Arr::flatten($data));

        $reduce = $flatten->map(function ($item) {
            return substr($item, -2);
        });

        $unique = $reduce->unique();

        return $unique->values()->all();
    }
}
