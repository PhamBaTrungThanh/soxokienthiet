<?php

namespace App\Jobs;

class GenerateImageJob extends Job
{
    const MATRIX_DIMENSION = 10;
    /**
     * Create a new job instance.
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
                if (1 === $cell) {
                    // fill
                    ImageFilledRectangle($image, $left, $top, $left + $this->cellSize, $top + $this->cellSize, $colorBlack);
                }
            }
        }

        imagepng($image, storage_path("images/single/{$this->fileName}.png"));

        imagedestroy($image);

        dispatch(new GenerateImageGridJob($this->fileName));
    }
}
