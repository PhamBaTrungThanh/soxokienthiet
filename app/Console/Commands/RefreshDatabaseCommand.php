<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Console\Traits\ListeningQueueTrait;
use App\Jobs\Crawls\ProcessingKetQuaPageJob;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshDatabaseCommand extends Command
{
    use ListeningQueueTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:database {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rerun image job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Queue::size('crawler') > 0) {
            $this->info('Continue crawling');
            $this->listenOnQueue('crawler');
        } else {
            $this->startCrawling();
        }
    }

    private function startCrawling()
    {
        $today = Carbon::now();

        try {
            $latestDate = app('redis')->get(config('app.lottery.latest_date'));
            throw_if(blank($latestDate), Exception::class, config('app.lottery.latest_date').' is empty');
            $latest = Carbon::parse(app('redis')->get(config('app.lottery.latest_date')));
        } catch (Exception $e) {
            app('redis')->set(config('app.lottery.latest_date'), config('app.lottery.oldest_date'));
            app('redis')->persist(config('app.lottery.latest_date'));
            $latest = Carbon::parse(config('app.lottery.oldest_date'));
            $this->error($e->getMessage());
        }
        $period = CarbonPeriod::create($latestDate, $today, CarbonPeriod::EXCLUDE_START_DATE);
        if ($today->hour <= 18 || $today->minute <= 30) {
            $period->excludeEndDate();
        }
        $this->table(['Today', 'Stored day', 'Days to crawl'], [
            [$today->format('Y-m-d'), $latest->format('Y-m-d'), $period->count()],
        ]);
        if ($period->count() > 0) {
            if ($this->confirm("Do you want to run crawler for {$period->count()} days")) {
                foreach ($period as $date) {
                    dispatch(new ProcessingKetQuaPageJob($date))->onQueue('crawler');
                }
                $this->listenOnQueue('crawler');
            } else {
                $this->info('Crawler canceled');
            }
        } else {
            $this->info('No data to crawl');
        }
    }
}
