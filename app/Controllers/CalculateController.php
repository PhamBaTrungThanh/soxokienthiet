<?php
namespace App\Controllers;

use App\Libraries\Chunking;
use App\Libraries\Collection;
use App\Libraries\Helpers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Lottery;

//const LIMITER_3 = 628;

class CalculateController
{
    public function index(Request $request)
    {
        if ($request->query("date")) {
            $lottery = (new Lottery)->get($request->query("date"));
            if (!$lottery) {
                die("Not in database");
            }
        } else {
            $lottery = (new Lottery())->latest();
        }
        $matches = [];
        $dates = [];
        //$LIMITER_3 = $lottery->date->weekOfYear * $lottery->date->dayOfWeek;
        //$LIMITER_3 = 500;
        $year = $lottery->date->year;
        $skip_date = $lottery->date->format("Y-m-d");
        $list = (new Chunking($lottery))->getGroupedChunks();
        
        // sanitize 1
        //dd($list);
        //$list = array_filter($list, function($item) { return $item > 1;});
        foreach ($list as $key => $weight) {
            $chunk = json_decode(redis()->get("chunk:{$key}"), true);
            
            foreach ($chunk as $date => $count) {
                $compare_date = Carbon::parse($date);
                if ($compare_date < $lottery->date) {
                    if (!isset($dates[$date])) {
                        $dates[$date] = 0;
                    }
                    $dates[$date] = $dates[$date] + ($count * $weight);
                    //$dates[$date]++;
                }


                // sanitize 2
            }
        }
    
        // limiter 3
        
        // // arsort($dates);
        $limiter = floor((1 / M_PI) * count($dates));
        // // $key = array_keys($dates)[$limiter];
        
        // // $LIMITER_3 = $dates[$key] + $lottery->date->weekOfYear * 2;
        

        $dates = array_slice($dates, $limiter);
        
        // $dates = array_filter($dates, function($item) { return ($item > 1000); });
        

        foreach ($dates as $date => $count) {
            $lotteria = (new Lottery())->get($date)->getNext();
            if ($lotteria) {
                $numbers = (new Collection($lotteria->value))->grouped;
                foreach ($numbers as $number => $value) {
                    if (!isset($matches[$number])) {
                        $matches[$number] = 0;
                    }
                    // $matches[$number] += $value * $count;
                    $matches[$number]++;
                }
            }
        }
        $result = [];
        /*
                foreach ($matches as $number => $value) {
                    if ($matches[$number] === 0) {
                        continue;
                    }
                    $index = (string) $number;
                    if ($index[0] === $index[1]) {
                        $matches[$number] = 0;
                        // $matches[$number] = $value * 2;
                        continue;
                    }
                    $oposite = strrev($index);
                    $matches[$number] = $value + $matches[$oposite];
                    $matches[$oposite] = 0;
                }
                $matches = array_filter($matches, function($item) { return ($item <> 0); });
                */
        arsort($matches);
        
        
        $resultLottery = $lottery->getNext();
        $group = [];
        foreach ($matches as $match => $value) {
            if (!isset($group[$value])) {
                $group[$value] = [$match];
            } else {
                $group[$value][] = $match;
            }
            if (count($group) === 10) {
                break;
            }
        }
        $count = 1;
        echo "<pre>";

        echo "-------------------\n";
        echo "| Dự đoán kết quả |\n";
        echo "| Ngày " . $lottery->date->addDay()->format("d/m/Y") ." |\n";
        echo "-------------------\n";
        foreach ($group as $predictions) {
            $header = false;
            foreach ($predictions as $prediction) {
                if ($header === false) {
                    echo "| " . Helpers::addOrdinalNumberSuffix($count);
                    if ($count < 10) {
                        echo  " ";
                    }
                    echo " |";
                    $header = true;
                } else {
                    if ($count < 10) {
                        echo "|      |";
                    } else {
                        echo "|     |";
                    }
                }
                
                echo "  {$prediction}  | ";
                if ($resultLottery) {
                    echo in_array($prediction, $resultLottery->value) ? "T" : "F";
                } else {
                    echo "N";
                }
                echo " |\n";
            }
            $count++;
            echo "-------------------\n";
        }

        /*
        $associator = new Apriori($support = 0.01, $confidence = 0.01);
        $associator->train($matches, $labels);
        dd($associator->getRules());
        */
    }
}
