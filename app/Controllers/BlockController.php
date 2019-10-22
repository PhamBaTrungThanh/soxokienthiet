<?php
namespace App\Controllers;

use App\Libraries\Chunking;
use App\Libraries\Collection;
use Phpml\Association\Apriori;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Lottery;

class BlockController
{
    public function compare($block, $compare_block, $tolerance)
    {
        $count = 0;
        $result = true;
        for ($i = strlen($block) - 1; $i >= 0; $i--) {
            if ($block[$i] !== $compare_block[$i]) {
                $count++;
            }
            if ($count > $tolerance) {
                $result = false;
                break;
            }
        }
        return $result;
    }
    public function index(Request $request)
    {
        header('Content-Type: application/json');
        if ($request->query("date")) {
            $lottery = (new Lottery)->get($request->query("date"));
        } else {
            $lottery = (new Lottery())->latest();
        }
        $block = (int) $request->query("block", "60");
        $tolerance = (int) $request->query("tolerance", "12");
        $number = sprintf("%'.02d", $request->query("number", "29"));
        $distance = (int) $request->query("distance", 0);
        
        $blocks = join(array_values(json_decode(redis()->get("block:{$number}"), true)), "");
        
        $compare_block = substr($blocks, -$block);
        
        $limit = strlen($blocks) - $block - 1;
        $result = [];
        for ($i = 0; $i <= $limit; $i++) {
            $cutter = substr($blocks, $i, $block);
            if ($this->compare($cutter, $compare_block, $tolerance)) {
                $result[$i] = $blocks[$i + $block];
            }
        }
        if (count($result) === 0) {
            echo json_encode([
                "status" => 0,
            ]);
        } else {
            $positives = 0;
            foreach ($result as $index => $hit) {
                if ($hit === "1") {
                    $positives++;
                }
            }
            
            echo json_encode([
                "status" => 1,
                "positives" => $positives,
                "total" => count($result),
            ]);
        }
    }
}
