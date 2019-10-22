<?php
namespace App\Controllers;

use Illuminate\Http\Request;
use App\Models\Lottery;
use App\Libraries\LunarDate;
use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;

class FetchController
{
    public function get(Request $request)
    {
        if ($request->query("force")) {
            try {
                $date = Carbon::createFromFormat("Y-m-d", $request->query("force"));
            } catch (InvalidArgumentException $e) {
                echo($e);
            }
            if ($date) {
                $lottery = $this->fetch($date);
                if (is_bool($lottery)) { // new year
                    view("fetch", ["is_new_year" => true]);
                } else {
                    view("fetch", ["lottery" => $lottery, "is_new_year" => false]);
                }
            }
        } else {
            $latest = (new Lottery)->latest();
            $lottery = $this->fetch($latest->date->addDay());
            $limitReached = $latest->date->addDay()->diffInDays() === 0;
            view("fetch", ["lottery" => $lottery, "limitReached" => $limitReached, "is_new_year" => false]);
        }
    }



    /*
     * Service functions
     */
    private function regex($regex, $data)
    {
        preg_match($regex, $data, $matches);
        $result = explode(" ", str_replace("<br>", " ", $matches[1]));
        return count($result) === 1 ? $result[0] : $result;
    }
    private function fetch($date)
    {
        $exists = Lottery::exists($date);
        if ($exists) {
            die("Dữ liệu ngày {$date->format("d/m/Y")} đã có trong hệ thống");
        }
        $sourceUrl = "https://xskt.com.vn/ket-qua-xo-so-theo-ngay/mien-bac-xsmb/{$date->format("d-m-Y")}.html";
        $client = new HttpClient();
        $httpResponse = $client->request("GET", $sourceUrl);
        if (strpos($httpResponse->getBody(), '<table class="result" id="MB0">') === false) {
            $lunar = ((new LunarDate)->toLunarDay($date));
            $lunarDay = "{$lunar[0]}-{$lunar[1]}";
            $lunarMonth = $lunar[1];
            //if (in_array($, ["29-12", "30-12", "1-1", "2-1", "3-1"])) {
            if ($lunarMonth == 12 || $lunarMonth == 1) {
                return true;
            } else {
                die("Không tìm thấy dữ liệu");
            }
        } else {
            $body = $httpResponse->getBody();
            $data = [];
            $data["jackpot"] = $this->regex("/<td title=\"Giải ĐB\">ĐB<\/td>\n<td><em>(\d+)<\/em><\/td>/", $body);
            $data["first_place"] = $this->regex("/<td title=\"Giải nhất\">G1<\/td>\n<td><p>(\d+)<\/p><\/td>/", $body);
            $data["second_place"] = $this->regex("/<td title=\"Giải nhì\">G2<\/td>\n<td><p>(.+?)<\/p><\/td>/", $body);
            $data["third_place"] = $this->regex("/<td rowspan=\"2\" title=\"Giải ba\">G3<\/td>\n<td rowspan=\"2\"><p>(.+?)<\/p><\/td>/", $body);
            $data["fourth_place"] = $this->regex("/<td title=\"Giải tư\">G4<\/td>\n<td><p>(.+?)<\/p><\/td>/", $body);
            $data["fifth_place"] = $this->regex("/<td rowspan=\"2\" title=\"Giải năm\">G5<\/td>\n<td rowspan=\"2\"><p>(.+?)<\/p><\/td>/", $body);
            $data["sixth_place"] = $this->regex("/<td title=\"Giải sáu\">G6<\/td>\n<td><p>(.+?)<\/p><\/td>/", $body);
            $data["seventh_place"] = $this->regex("/<td title=\"Giải bảy\">G7<\/td>\n<td><p>(.+?)<\/p><\/td>/", $body);
            

            /**
             * Sanding values
             */
            $values = [];
            $full_values = [$data["jackpot"], $data["first_place"]];
            $full_values = array_merge($full_values, $data["second_place"], $data["third_place"], $data["fourth_place"], $data["fifth_place"], $data["sixth_place"], $data["seventh_place"]);
            for ($i = 26; $i >= 0; $i--) {
                $values[$i] = substr($full_values[$i], -2);
            }
            $lottery = new Lottery;
            $lottery->date = $date;
            $lottery->value = array_values($values);
            $lottery->save();
            return $lottery;
        }
    }
}
