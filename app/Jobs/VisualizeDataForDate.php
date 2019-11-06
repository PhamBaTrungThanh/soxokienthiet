<?php

namespace App\Jobs;

use Exception;
use Illuminate\Support\Arr;

class VisualizeDataForDate extends Job
{

    const MATRIX_DIMENSION = 10;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $date;

    protected $tries = 1;
    public function __construct(string $date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $key = sprintf("%s:%s", env('LOTTERY_KEY'), $this->date);
        $storedData = app('redis')->get($key);
        if (trim($storedData) === '') {
            throw new Exception("No data found for key: " . $key);
        }
        $sanitizedData = $this->sanitizeData($storedData);

        $map = $this->generateMap($sanitizedData);

        dispatch(new GenerateImageJob($this->date, $map));
    }
    private function sanitizeData($data)
    {
        $data = json_decode($data);

        $flatten = collect(Arr::flatten($data));

        $reduce = $flatten->map(function ($item) {
            return substr($item, -2);
        });

        return $reduce;
    }
    private function generateMap($sanitizedData)
    {
        $map = [];
        // generate empty matrix
        for ($row = 0; $row < self::MATRIX_DIMENSION; $row++) {
            $map[$row] = [];
            for ($col = 0; $col < self::MATRIX_DIMENSION; $col++) {
                $map[$row][$col] = 0;
            }
        }

        // loop through data and make a dot for each value
        foreach ($sanitizedData as $dimension) {
            $row = $dimension[0];
            $col = $dimension[1];
            $map[$row][$col] = 1;
        }
        return $map;
    }
}
