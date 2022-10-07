<?php

namespace App\Console\Traits;

use Illuminate\Support\Facades\Queue;

trait ListeningQueueTrait
{
    public function listenOnQueue(string $queueName)
    {
        $bar = $this->output->createProgressBar(Queue::size($queueName));
        $lastCount = 0;
        while (true) {
            $jobLeft = Queue::size($queueName);
            if (0 === $jobLeft) {
                break;
            }
            $advance = 0 === $lastCount ? 0 : $lastCount - $jobLeft;
            $bar->advance($advance);
            $lastCount = $jobLeft;
            sleep(1);
        }
        $bar->finish();
        $this->info("\nProcess completed!");
    }
}
