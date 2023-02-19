<?php

namespace Megaads\DealsPage\Commands;

use Illuminate\Console\Command;

class MigrateExecution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deal-page:migrate {state?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stateOption = $this->argument('state');
        $output = "";
        switch ($stateOption) {
            case "status":
                    \Artisan::call("migrate:status", [
                        "--path" => "vendor/megaads/deals-page/src/Migrations"
                    ]);
                    $output = \Artisan::output();
                break;
            default:
                    \Artisan::call("migrate", [
                        "--path" => "vendor/megaads/deals-page/src/Migrations/"
                    ]);
                    $output = \Artisan::output();
                break;
        }
        echo $output;
    }
}
