<?php

namespace App\Jobs\Crawls;

use App\Jobs\Job;
use Carbon\Carbon;

class StartCrawlingDataJob extends Job
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
        dispatch((new ProcessingCrawledPageJob($next_date_to_crawl))->chain([new self()]));
    }

    public function getLatestCrawledDate()
    {
        $latest = app('redis')->get(config('app.lottery.latest_date'));
        if (!$latest) {
            app('redis')->set(config('app.lottery.latest_date'), config('app.lottery.oldest_date'));
            app('redis')->persist(config('app.lottery.latest_date'));

            return Carbon::parse(config('app.lottery.oldest_date'));
        }

        return Carbon::parse($latest);
    }
}
