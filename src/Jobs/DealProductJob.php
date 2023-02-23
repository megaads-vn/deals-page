<?php

namespace Megaads\DealsPage\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DealProductJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appUrl = \Config::get('deals-page.app_url');
        sendHttpRequest($appUrl . "/service/deal/schedule-bulk-create", "POST", [], ["Authorization: Basic YXBpOjEyM0AxMjNh"]);
    }
}
