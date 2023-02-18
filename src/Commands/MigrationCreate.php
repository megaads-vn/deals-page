<?php

namespace Megaads\DealsPage\Commands;

use Illuminate\Console\Command;

class MigrationCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deal-page:make:migration {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deals page package create migration command';

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
        \Artisan::call('make:migration', [
            'name' => $this->argument('name'),
             '--path' => 'vendor/megaads/deals-page/src/Migrations'
        ]);
        $output = \Artisan::output();
        echo $output;
    }
}
