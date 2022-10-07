<?php

namespace App\Jobs\Distributions;

use App\Jobs\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ImageClassificationJob extends Job
{
    public $keys = [];
    public $classes;

    public function __construct()
    {
        $this->keys = app('redis')->keys(config('app.lottery.key').':*');
        $this->classes = (object) [
            'generic' => array_fill_keys($this->prepareIndex(), []),
            'jackpot' => array_fill_keys($this->prepareIndex(), []),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->processDates();
        $this->fireImageFolderDistributionJob();
    }

    public function processDates()
    {
        foreach ($this->keys as $key) {
            $date = Str::after($key, config('app.lottery.key').':');
            $nextDayKey = $this->nextDayHasValue($date);
            if (!$nextDayKey) {
                continue;
            }
            $classes = $this->getClassesForCurrentKey($key);
            if (false === $classes) {
                continue;
            }

            foreach ($classes->generic as $class) {
                $this->classes->generic[$class][] = $date;
            }

            $this->classes->jackpot[$classes->jackpot][] = $date;
        }
    }

    /**
     * Prepare index from '00' to '99.
     */
    public function prepareIndex()
    {
        $keys = [];
        foreach (range(0, 99) as $index) {
            $keys[] = sprintf('%02d', $index);
        }

        return $keys;
    }

    private function fireImageFolderDistributionJob()
    {
        foreach ($this->classes->generic as $class => $classData) {
            dispatch(new ImageFolderDistributionJob($classData, $class, 'generic'));
        }

        // foreach ($this->classes->jackpot as $class => $jackpotData) {
        //     dispatch(new ImageFolderDistributionJob($jackpotData, $class, 'jackpot'));
        // }
    }

    /**
     * Flatten data.
     *
     * @param mixed $data
     *
     * @return array
     */
    private function flatten($data)
    {
        $flatten = collect(Arr::flatten($data));

        $reduce = $flatten->map(function ($item) {
            return substr($item, -2);
        });

        $unique = $reduce->unique();

        return $unique->values()->all();
    }

    /**
     * Check if next day has lottery result.
     *
     * @param mixed $date
     * @param mixed $key
     *
     * @return string $nextDayKey
     */
    private function nextDayHasValue($date)
    {
        $nextDay = date('Y-m-d', strtotime("{$date} + 1 day"));
        $nextDayKey = sprintf('%s:%s', config('app.lottery.key'), $nextDay);

        return in_array($nextDayKey, $this->keys) ? $nextDayKey : false;
    }

    /**
     * Get current image classes by loading next day lottery's result and trim it down to last 2 digits, then return array of them
     * separated by 2, 'jackpot' and 'generic'.
     *
     * @return stdClass
     */
    private function getClassesForCurrentKey(string $key)
    {
        $lotteryResult = json_decode(app('redis')->get($key));

        if (blank($lotteryResult)) {
            return false;
        }

        return (object) [
            'generic' => $this->flatten($lotteryResult),
            'jackpot' => substr($lotteryResult->jackpot, -2),
        ];
    }
}
