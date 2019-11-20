<?php

namespace App\Jobs\Crawls;

use Carbon\Carbon;

class StartCrawlerJob extends Job
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $latest_date_crawled = $this->getLatestCrawledDate();

        if ($latest_date_crawled->diffInDays(Carbon::now(), false) < 0) {
            app('log')->info($latest_date_crawled);
            $this->delete();

            return null;
        }
        if ($latest_date_crawled->isToday()) {
            $rollTime = Carbon::today()->setHour(18)->setMinute(40);
            if ($latest_date_crawled->lessThan($rollTime)) {
                info('Roll time invalid', [$latest_date_crawled]);
                $this->delete();

                return null;
            }
        }

        $next_date_to_crawl = $latest_date_crawled->addDay();
        app('log')->info('Crawl for '.$next_date_to_crawl->toString());
        dispatch((new KetQuaPageCrawlerJob($next_date_to_crawl))->chain([new self()]));
    }

    public function getLatestCrawledDate()
    {
        $latest = app('redis')->get(env('OPTION_LATEST_DATE_CRAWLED'));
        if (!$latest) {
            app('redis')->set(env('OPTION_LATEST_DATE_CRAWLED'), env('LOTTERY_OLDEST_DATE'));
            app('redis')->persist(env('OPTION_LATEST_DATE_CRAWLED'));

            return Carbon::parse(env('LOTTERY_OLDEST_DATE'));
        }

        return Carbon::parse($latest);
    }
}
