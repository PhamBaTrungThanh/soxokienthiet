<?php

namespace App\Jobs\Generates;

use App\Jobs\Job;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class GenerateImageGridJob extends Job
{
    const MATRIX_DIMENSION = 10;

    /**
     * Create a new job instance.
     */
    public $key;

    public $tilePerRow;

    public $gridSize;

    public $shouldProcess = true;
    public $tileSize;
    public $skipEmpty;

    public $date;

    public function __construct(string $key = 'lottery:2013-11-19', bool $skipEmpty = false)
    {
        $this->tilePerRow = config('app.image.row_grid');

        $this->tileSize = self::MATRIX_DIMENSION * config('app.image.cell_size');

        $this->gridSize = $this->tilePerRow * $this->tileSize;

        $this->key = $key;

        $this->date = Carbon::parse(Str::after($this->key, config('app.lottery.key').':'));

        $this->skipEmpty = $skipEmpty;
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
        try {
            $totalImageNeeded = $this->tilePerRow * $this->tilePerRow;
            $pathList = [];
            info("Make grid for date: {$this->date->format('Y-m-d')}");
            if ($this->skipEmpty) {
                $currentDateValue = app('redis')->get($this->key);
                if ('[]' === $currentDateValue) {
                    throw new Exception("Date {$this->date->format('Y-m-d')} is empty. Skip this date.");
                }
            }
            for ($imageIndex = $totalImageNeeded - 1; $imageIndex >= 0; --$imageIndex) {
                $dateToLoad = $this->date->clone()->subDays($imageIndex);

                $fileNameFromDate = "{$dateToLoad->format('Y-m-d')}";

                $filePath = storage_path("images/single/{$fileNameFromDate}.png");

                if (!file_exists($filePath)) {
                    throw new Exception("Image from path '".$filePath."' does not exits. Skip this date.");
                }
                if ($this->skipEmpty) {
                    $dateKey = config('app.lottery.key').':'.$fileNameFromDate;
                    $dateValue = app('redis')->get($dateKey);
                    if ('[]' === $dateValue) {
                        throw new Exception("Related date {$fileNameFromDate} is empty. Skip this date.");
                    }
                }

                $pathList[] = $filePath;
            }
        } catch (Exception $e) {
            app('log')->warning($e->getMessage());
            $this->shouldProcess = false;
        }

        return $pathList;
    }

    public function handle()
    {
        $imageList = $this->makeImageList();

        if ($this->shouldProcess) {
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
        } else {
            $this->delete();
        }
    }
}
