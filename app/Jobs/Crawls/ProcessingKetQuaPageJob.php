<?php

namespace App\Jobs\Crawls;

use App\Jobs\Generates\ChainGenerateJob;
use App\Jobs\Job;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ProcessingKetQuaPageJob extends Job
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

        return $this->storeData($data);
        // dispatch(new ChainGenerateJob($this->queryDate->format('Y-m-d')));
    }

    private function getHTML()
    {
        $client = new Client();
        $sourceUrl = sprintf('http://ketqua.net/in-truyen-thong.php?ngay=%s', $this->queryDate->format('d-m-Y'));
        $response = $client->request('GET', $sourceUrl);

        return $response->getBody();
    }

    private function parseHTML($body)
    {
        if (false === strpos($body, '<h2 class="viethoa printh2 vietdam">Kết quả xổ số Truyền Thống</h2>')) {
            return [];
        }
        $data = [];
        $data['jackpot'] = $this->regex('/<td id="rs_0_0" colspan="12" style="width:72%;" class="vietdam chu28" rs_len="5">(\\d+)<\\/td>/', $body);
        $data['first_place'] = $this->regex('/<td id="rs_1_0" colspan="12" style="width:72%;" class="vietdam chu20" rs_len="5">(\\d+)<\\/td>/', $body);
        $data['second_place'] = $this->regex('/<td id="rs_2_\\d" colspan="6" style="width:36%;" class="vietdam chu20" rs_len="5">(\\d+)<\\/td>/', $body);
        $data['third_place'] = $this->regex('/<td id="rs_3_\\d" colspan="4" style="width:24%;" class="vietdam chu20" rs_len="5">(\\d+)<\\/td>/', $body);
        $data['fourth_place'] = $this->regex('/td id="rs_4_\\d" colspan="3" style="width:18%;" class="vietdam chu20" rs_len="4">(\\d+)<\\/td>/', $body);
        $data['fifth_place'] = $this->regex('/<td id="rs_5_\\d" colspan="4" style="width:24%;" class="vietdam chu20" rs_len="4">(\\d+)<\\/td>/', $body);
        $data['sixth_place'] = $this->regex('/<td id="rs_6_\\d" colspan="4" style="width:24%;" class="vietdam chu20" rs_len="3">(\\d+)<\\/td>/', $body);
        $data['seventh_place'] = $this->regex('/<td id="rs_7_\\d" colspan="3" style="width:18%;" class="vietdam chu20" rs_len="2">(\\d+)<\\/td>/', $body);

        return $data;
    }

    private function storeData($data)
    {
        $key = sprintf('%s:%s', config('app.lottery.key'), $this->queryDate->format('Y-m-d'));
        app('redis')->set($key, json_encode($data));
        app('redis')->persist($key);
        app('redis')->set(config('app.lottery.latest_date'), $this->queryDate->format('Y-m-d'));
        app('redis')->persist(config('app.lottery.latest_date'));

        return true;
    }

    // Service functions
    private function regex($regex, $data)
    {
        try {
            preg_match_all($regex, $data, $matches);

            return 1 === count($matches[1]) ? $matches[1][0] : $matches[1];
        } catch (Exception $e) {
            report($e);

            return '';
        }
    }
}
