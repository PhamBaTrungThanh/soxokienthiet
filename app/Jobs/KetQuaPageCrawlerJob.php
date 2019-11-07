<?php

namespace App\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Client;

class KetQuaPageCrawlerJob extends Job
{
    /**
     * Create a new job instance.
     */
    public $queryDate;
    public $loop;

    public function __construct(Carbon $date)
    {
        $this->queryDate = $date;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $body = $this->getHTML();
        $data = $this->parseHTML($body);
        $result = $this->storeData($data);
        dispatch(new VisualizeDataForDate($this->queryDate->format('Y-m-d')));

        return $result;
    }

    private function getHTML()
    {
        $client = new Client();
        $sourceUrl = sprintf('https://xskt.com.vn/ket-qua-xo-so-theo-ngay/mien-bac-xsmb/%s.html', $this->queryDate->format('d-m-Y'));
        $response = $client->request('GET', $sourceUrl);

        return $response->getBody();
    }

    private function parseHTML($body)
    {
        if (false === strpos($body, '<table class="result" id="MB0">')) {
            return [];
        }
        $data = [];
        $data['jackpot'] = $this->regex("/<td title=\"Giải ĐB\">ĐB<\\/td>\n<td><em>(\\d+)<\\/em><\\/td>/", $body);
        $data['first_place'] = $this->regex("/<td title=\"Giải nhất\">G1<\\/td>\n<td><p>(\\d+)<\\/p><\\/td>/", $body);
        $data['second_place'] = $this->regex("/<td title=\"Giải nhì\">G2<\\/td>\n<td><p>(.+?)<\\/p><\\/td>/", $body);
        $data['third_place'] = $this->regex("/<td rowspan=\"2\" title=\"Giải ba\">G3<\\/td>\n<td rowspan=\"2\"><p>(.+?)<\\/p><\\/td>/", $body);
        $data['fourth_place'] = $this->regex("/<td title=\"Giải tư\">G4<\\/td>\n<td><p>(.+?)<\\/p><\\/td>/", $body);
        $data['fifth_place'] = $this->regex("/<td rowspan=\"2\" title=\"Giải năm\">G5<\\/td>\n<td rowspan=\"2\"><p>(.+?)<\\/p><\\/td>/", $body);
        $data['sixth_place'] = $this->regex("/<td title=\"Giải sáu\">G6<\\/td>\n<td><p>(.+?)<\\/p><\\/td>/", $body);
        $data['seventh_place'] = $this->regex("/<td title=\"Giải bảy\">G7<\\/td>\n<td><p>(.+?)<\\/p><\\/td>/", $body);

        return $data;
    }

    private function storeData($data)
    {
        $key = sprintf('%s:%s', env('LOTTERY_KEY'), $this->queryDate->format('Y-m-d'));
        app('redis')->set($key, json_encode($data));
        app('redis')->persist($key);
        app('redis')->set(env('OPTION_LATEST_DATE_CRAWLED'), $this->queryDate->format('Y-m-d'));
        app('redis')->persist(env('OPTION_LATEST_DATE_CRAWLED'));

        return true;
    }

    // Service functions
    private function regex($regex, $data)
    {
        preg_match($regex, $data, $matches);
        $result = explode(' ', str_replace('<br>', ' ', $matches[1]));

        return 1 === count($result) ? $result[0] : $result;
    }
}
