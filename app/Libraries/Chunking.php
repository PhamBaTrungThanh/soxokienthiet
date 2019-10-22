<?php


namespace App\Libraries;

use App\Models\Lottery;

function addToChunk($chunks, $array)
{
    $result = [];
    for ($i = count($chunks) - 1; $i >= 0; $i--) {
        for ($j = 26; $j >= 0; $j--) {
            $result[] = "{$chunks[$i]}-{$array[$j]}";
        }
    }
    return $result;
}


class Chunking
{
    public $chunks = [];

    public function __construct(Lottery $lottery)
    {
        $previousLottery = $lottery->getPrevious();
        $previousPreviousLottery = $previousLottery->getPrevious();
        $chunks = $lottery->value;
        $chunks = addToChunk($chunks, $previousLottery->value);
        $chunks = addToChunk($chunks, $previousPreviousLottery->value);
        $this->chunks = $chunks;
        return $this;
    }
    public function getChunks()
    {
        return $this->chunks;
    }
    public function getGroupedChunks()
    {
        $grouped = [];
        for ($i = count($this->chunks) - 1; $i >= 0; $i--) {
            if (!isset($grouped[$this->chunks[$i]])) {
                $grouped[$this->chunks[$i]] = 0;
            }
            $grouped[$this->chunks[$i]]++;
        }
        return $grouped;
    }
}
