<?php

namespace Megaads\DealsPage\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CatalogJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $pageId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appUrl = \Config::get('deals-page.app_url');
        sendHttpRequest($appUrl . "/service/catalog/bulk-create", "POST", ["pageId" => $this->pageId]);
    }
}
