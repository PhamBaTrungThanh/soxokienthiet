<?php

namespace App\Jobs\Generates;

use App\Jobs\Job;
use Exception;
use Illuminate\Support\Arr;

class GenerateImageJob extends Job
{
    const MATRIX_DIMENSION = 10;
    /**
     * Create a new job instance.
     */
    public $map;
    public $cellSize;
    public $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->cellSize = config('app.image.cell_size');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $key = sprintf('%s:%s', config('app.lottery.key'), $this->date);
        $storedData = app('redis')->get($key);
        if ('' === trim($storedData)) {
            throw new Exception('No data found for key: '.$key);
        }
        $data = $this->sanitizeData($storedData);
        $jackpot = $this->getJackpot($storedData);

        $this->map = $this->generateMap($data, $jackpot);

        $this->makeImage();
    }

    private function makeImage()
    {
        $imageDimension = $this->cellSize * self::MATRIX_DIMENSION;

        $image = ImageCreate($imageDimension, $imageDimension);
        $colorWhite = ImageColorAllocate($image, 0xFF, 0xFF, 0xFF);
        $colorBlack = ImageColorAllocate($image, 0x00, 0x00, 0x00);
        $colorJackpot = ImageColorAllocate($image, 0xFF, 0x00, 0x00);

        foreach ($this->map as $rowIndex => $row) {
            $top = $rowIndex * $this->cellSize;
            foreach ($row as $cellIndex => $cell) {
                $left = $cellIndex * $this->cellSize;
                if (0 !== $cell) {
                    // fill
                    ImageFilledRectangle($image, $left, $top, $left + $this->cellSize, $top + $this->cellSize, 1 === $cell ? $colorBlack : $colorJackpot);
                } else {
                    ImageFilledRectangle($image, $left, $top, $left + $this->cellSize, $top + $this->cellSize, 1 === $colorWhite);
                }
            }
        }

        imagepng($image, storage_path("images/single/{$this->fileName}.png"));

        imagedestroy($image);
    }

    private function sanitizeData($data)
    {
        $data = json_decode($data);

        $flatten = collect(Arr::flatten($data));

        return $flatten->map(function ($item) {
            return substr($item, -2);
        });
    }

    private function getJackpot($data)
    {
        $data = json_decode($data);

        return substr(data_get($data, 'jackpot'), -2);
    }

    private function generateMap($sanitizedData, $jackpot)
    {
        $map = [];
        // generate empty matrix
        for ($row = 0; $row < self::MATRIX_DIMENSION; ++$row) {
            $map[$row] = [];
            for ($col = 0; $col < self::MATRIX_DIMENSION; ++$col) {
                $map[$row][$col] = 0;
            }
        }

        // loop through data and make a dot for each value
        foreach ($sanitizedData as $dimension) {
            $row = $dimension[0];
            $col = $dimension[1];
            if ($jackpot === $row.$col) {
                $map[$row][$col] = 2;
            } else {
                $map[$row][$col] = 1;
            }
        }

        return $map;
    }
}
