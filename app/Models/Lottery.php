<?php
namespace App\Models;

use Carbon\Carbon;

class Lottery
{
    public $key;
    private $attributes = [];
    protected $redis;
    public static function exists($date)
    {
        $date = Carbon::parse($date)->format("Y-m-d");
        return redis()->exists("lottery:{$date}");
    }
    public function get($date = null)
    {
        if ($date === null) {
            die("no date");
        } else {
            $this->attributes["date"] = $date;
        }
        
        if (!static::exists($this->attributes["date"])) {
            return false;
        }
        return $this->getByDate($this->attributes["date"]);
    }

    public function latest()
    {
        $this->attributes["date"] = Carbon::now();
        if (!static::exists($this->attributes["date"])) {
            $this->attributes["date"] = $this->findLatestDate($this->attributes["date"]);
        }
        return $this->getByDate($this->attributes["date"]);
    }
    public function getByDate($date)
    {
        $this->attributes["date"] = Carbon::parse($date);
        $this->attributes["value"] = json_decode(redis()->get("lottery:{$this->attributes["date"]->format("Y-m-d")}"));
        return $this;
    }
    public function __get($name)
    {
        return $this->attributes[$name];
    }
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function save()
    {
        redis()->set("lottery:{$this->attributes["date"]->format("Y-m-d")}", json_encode($this->attributes["value"]));
    }
    protected function findLatestDate($date)
    {
        $date = $date->subDay();
        while (!static::exists($date)) {
            $date = $date->subDay();
        }
        return $date;
    }
    protected function findNearestDate($order = "DESC")
    {
        $date = ($order === "ASC") ? $this->attributes["date"]->copy()->addDay() : $this->attributes["date"]->copy()->subDay();
        if ($order === "ASC") {
            if ($date->diffInDays(Carbon::now(), false) <= 0) {
                return false;
            }
        }

        while (!static::exists($date)) {
            $date = ($order === "ASC") ? $date->addDay() : $date->subDay();
        }
        return $date;
    }

    public function getPrevious()
    {
        return  (new self)->getByDate($this->findNearestDate());
    }
    public function getNext()
    {
        $next_date = $this->findNearestDate("ASC");
        if (!$next_date) {
            return false;
        }
        return  (new self)->getByDate($this->findNearestDate("ASC"));
    }
}
