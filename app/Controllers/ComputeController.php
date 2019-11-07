<?php

namespace App\Controllers;

use App\Libraries\Chunking;
use App\Models\Chunk;
use App\Models\Lottery;
use Illuminate\Http\Request;

class ComputeController
{
    public function index(Request $request)
    {
        if (!$request->query('date')) {
            die('Không có dữ liệu ngày');
        }
        $lottery = Lottery::where('rolled_at', $request->query('date'))->first();
        if (!$lottery) {
            die('Không có dữ liệu của ngày '.$request->query('date'));
        }
        $chunks = (new Chunking($lottery))->getChunks();
        $uniques = '|'.implode('|', array_values(array_unique($chunks))).'|';
        $chunk = new Chunk();
        $chunk->date = $lottery->rolled_at;
        $chunk->values = $chunks;
        $chunk->uniques = $uniques;
        $chunk->save();
        $nextLottery = Lottery::whereDate('rolled_at', '>', $lottery->rolled_at)->where('is_new_year', false)->orderBy('rolled_at', 'ASC')->first();
        view('compute', ['nextLottery' => $nextLottery, 'date' => $request->query('date')]);
    }
}
