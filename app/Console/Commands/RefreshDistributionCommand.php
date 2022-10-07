<?php

/**
 * PHP version >= 7.0.
 *
 * @category Console_Command
 */

namespace App\Console\Commands;

use App\Console\Traits\ListeningQueueTrait;
use App\Jobs\Distributions\ImageClassificationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class RefreshDistributionCommand extends Command
{
    use ListeningQueueTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'refresh:distribution';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-run distribution job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Queue::size('distributor') > 0) {
            $this->info('Continue distributor job');
            $this->listenOnQueue('distributor');
        } else {
            $this->startRegenerate();
        }
    }

    private function startRegenerate()
    {
        if ($this->confirm('Do you want to re-run distribution job?')) {
            $this->info('Make jobs, please wait.');
            dispatch(new ImageClassificationJob())->onQueue('distributor');

            $this->listenOnQueue('distributor');
        } else {
            $this->info('Command Cancelled');
        }
    }
}
