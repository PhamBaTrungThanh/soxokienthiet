<?php

namespace App\Jobs\Generates;

use Carbon\Carbon;
use Exception;

class GenerateImageGridJob extends Job
{
    const MATRIX_DIMENSION = 10;

    /**
     * Create a new job instance.
     */
    public $date;

    public $tilePerRow;

    public $gridSize;

    public $tileSize;

    public function __construct(string $date)
    {
        $this->date = Carbon::parse($date);

        $this->tilePerRow = env('IMAGE_ROW_GRID', 3);

        $this->tileSize = self::MATRIX_DIMENSION * env('IMAGE_CELL_SIZE', 5);

        $this->gridSize = $this->tilePerRow * $this->tileSize;
    }

    /**
     * Return coordinates from index.
     *
     * @return array [$x, $y]
     */
    public function makeCoordinatesFromIndex(int $index)
    {
        $x = ($index % $this->tilePerRow) * $this->tileSize;
        $y = floor($index / $this->tilePerRow) * $this->tileSize;

        return [$x, $y];
    }

    /**
     * Making image grid from file list.
     *
     * @param array $fileList
     */
    public function makeImageGrid($fileList)
    {
        $gridImage = imagecreatetruecolor($this->gridSize, $this->gridSize);
        $bgColor = imagecolorallocate($gridImage, 255, 255, 255); // white
        imagefill($gridImage, 0, 0, $bgColor);

        return $gridImage;
    }

    /**
     * Check image exits by minus date from $this->date.
     *
     * @return array $pathList
     */
    public function makeImageList()
    {
        $totalImageNeeded = $this->tilePerRow * $this->tilePerRow;
        $pathList = [];

        for ($imageIndex = $totalImageNeeded - 1; $imageIndex >= 0; --$imageIndex) {
            $dateToLoad = $this->date->clone()->subDays($imageIndex);

            $fileNameFromDate = "{$dateToLoad->format('Y-m-d')}";

            $filePath = storage_path("images/single/{$fileNameFromDate}.png");

            if (!file_exists($filePath)) {
                throw new Exception("Image from path '".$filePath."' does not exits. Aborting.");
            }

            $pathList[] = $filePath;
        }

        return $pathList;
    }

    public function handle()
    {
        $imageList = $this->makeImageList();

        $gridImage = $this->makeImageGrid($imageList);

        foreach ($imageList as $index => $imagePath) {
            list($x, $y) = $this->makeCoordinatesFromIndex($index);
            $tileImage = imagecreatefrompng($imagePath);
            imagecopy($gridImage, $tileImage, $x, $y, 0, 0, $this->tileSize, $this->tileSize);
            imagedestroy($tileImage);
        }

        $directoryPath = storage_path("images/grids/{$this->tilePerRow}x{$this->tilePerRow}/");

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath);
        }
        $filePath = $directoryPath.$this->date->format('Y-m-d').'.png';

        imagepng($gridImage, $filePath);
    }
}
