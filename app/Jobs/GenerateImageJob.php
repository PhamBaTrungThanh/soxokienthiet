<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Jobs\KetQuaPageCrawlerJob;

class GenerateImageJob extends Job
{
    const MATRIX_DIMENSION = 10;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $map;
    public $cellSize;
    public $fileName;
    public function __construct(string $fileName, array $map)
    {
        $this->fileName = $fileName;
        $this->map = $map;
        $this->cellSize = env('IMAGE_CELL_SIZE', 5); // px
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $imageDimension = $this->cellSize * self::MATRIX_DIMENSION;


        $image = ImageCreate($imageDimension, $imageDimension);
        $colorWhite = ImageColorAllocate($image, 0xFF, 0xFF, 0xFF);
        $colorBlack = ImageColorAllocate($image, 0x00, 0x00, 0x00);

        foreach ($this->map as $rowIndex => $row) {
            $top = $rowIndex * $this->cellSize;
            foreach ($row as $cellIndex => $cell) {
                $left = $cellIndex * $this->cellSize;
                if ($cell === 1) {
                    // fill
                    ImageFilledRectangle($image, $left, $top, $left + $this->cellSize, $top + $this->cellSize, $colorBlack);
                }
            }
        }

        imagepng($image, storage_path("image/single/{$this->fileName}.png"));
    }
}
