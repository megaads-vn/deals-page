<?php

namespace Megaads\DealsPage\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DealProductJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $catalogId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $catalogId)
    {
        $this->catalogId = $catalogId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        sendHttpRequest('https://couponforless.test/service/deal/testing-queue', 'GET');
    }
}
