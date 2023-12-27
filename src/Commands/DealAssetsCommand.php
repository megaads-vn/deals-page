<?php

namespace Megaads\DealsPage\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DealAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deal-page:asset {options?}';

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
        $this->commandOutput = '';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argument = $this->argument('options');
        $artisanCommand = 'php artisan vendor:publish --provider="Megaads\DealsPage\Providers\DealsPageProvider" --tag=assets';
        if ($argument == 'force') {
            $artisanCommand .= ' --force';
        }
        $process = new Process($artisanCommand);
        $process->run(function($err, $data) {
            $this->commandOutput = $data;
        });
        echo $this->commandOutput;
    }
}
