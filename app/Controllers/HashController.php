<?php

namespace App\Controllers;

use App\Libraries\Chunking;
use App\Models\Lottery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HashController
{
    public function number($num)
    {
        if ($num <= 9) {
            return '0'.$num;
        }

        return "{$num}";
    }

    public function index(Request $request)
    {
        if ($request->query('date')) {
            $lottery = (new Lottery())->get($request->query('date'));
            if (!$lottery) {
                die('Không tìm thấy ngày.');
            }
        } else {
            $date = redis()->get('chunk:next');
            if (!$date) {
                $date = '2006-04-19';
            }
            $date = Carbon::parse($date);

            if ($date > (new Lottery())->latest()->date) {
                die('End of Database.');
            }
            $lottery = (new Lottery())->get($date);
        }

        $chunks = (new Chunking($lottery))->getGroupedChunks();
        $day = $lottery->date->format('Y-m-d');
        $nextDay = false;
        foreach ($chunks as $chunk => $count) {
            $exists = redis()->exists("chunk:{$chunk}");
            if ($exists) {
                $dates = json_decode(redis()->get("chunk:{$chunk}"), true);
            } else {
                $dates = [];
            }
            $dates[$day] = $count;
            redis()->set("chunk:{$chunk}", json_encode($dates));
        }
        $defaults = [];
        $blocks = [];
        for ($i = 0; $i <= 99; ++$i) {
            $defaults[] = sprintf("%'.02d", $i);
        }
        $values = array_unique($lottery->value);
        $block_0 = array_diff($defaults, $values);
        foreach ($block_0 as $block) {
            $value = json_decode(redis()->get("block:{$block}"), true);
            $value[$day] = 0;
            redis()->set("block:{$block}", json_encode($value));
        }
        foreach ($values as $block) {
            $value = json_decode(redis()->get("block:{$block}"), true);
            $value[$day] = 1;
            redis()->set("block:{$block}", json_encode($value));
        }
        $nextLottery = $lottery->getNext();
        if ($nextLottery) {
            $nextDay = $nextLottery->date->format('Y-m-d');
            redis()->set('chunk:next', $nextDay);
        }
        view('hash', ['day' => $day, 'nextDay' => $nextDay]);
    }
}
