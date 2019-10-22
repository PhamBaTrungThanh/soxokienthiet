<?php
namespace App\Libraries;

class Collection
{
    public $array;
    public $grouped;
    public function __construct(array $array)
    {
        $this->array = $array;
        $this->grouped = $this->makeGroup();
    }
    public function makeGroup()
    {
        $result = [];

        for ($i = 0, $run = count($this->array) - 1; $i <= $run; $i++) {
            if (!isset($result[$this->array[$i]])) {
                $result[$this->array[$i]] = 0;
            }
            $result[$this->array[$i]]++;
        }
        return $result;
    }
}
