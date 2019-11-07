<?php

namespace App\Jobs;

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
     * Execute the job.
     */
    public function test()
    {
        $output_img_name = 'output.png';
        $path = 'img/';
        $files = scandir($path);
        $files = array_values(array_diff(scandir($path), ['.', '..']));

        $thumbSize = 300;
        $tileWidth = $tileHeight = 50;
        $numberOfTiles = 9;
        $numberOfTilesPerRow = 3;

        $pxBetweenTiles = 0;
        $leftOffSet = $topOffSet = 0;

        $mapWidth = $mapHeight = ($tileWidth + $pxBetweenTiles) * floor($numberOfTiles / $numberOfTilesPerRow);

        $mapImage = imagecreatetruecolor($mapWidth, $mapHeight);
        $bgColor = imagecolorallocate($mapImage, 0, 0, 0);
        imagefill($mapImage, 0, 0, $bgColor);

        function indexToCoords($index)
        {
            global $tileWidth, $pxBetweenTiles, $leftOffSet, $topOffSet, $numberOfTiles, $numberOfTilesPerRow;

            $x = ($index % $numberOfTilesPerRow) * ($tileWidth + $pxBetweenTiles) + $leftOffSet;
            $y = floor($index / $numberOfTilesPerRow) * ($tileWidth + $pxBetweenTiles) + $topOffSet;

            return [$x, $y];
        }

        $thumbImage = imagecreatetruecolor($thumbSize, $thumbSize);
        imagecopyresampled($thumbImage, $mapImage, 0, 0, 0, 0, $thumbSize, $thumbSize, $mapWidth, $mapWidth);

        header('Content-type: image/png');
        imagepng($thumbImage, $output_img_name);
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

        info($directoryPath);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath);
        }
        $filePath = $directoryPath.$this->date->format('Y-m-d').'.png';

        imagepng($gridImage, $filePath);
    }
}
