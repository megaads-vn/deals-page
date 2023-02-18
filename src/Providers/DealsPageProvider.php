<?php

namespace Megaads\DealsPage\Providers;

use Illuminate\Support\ServiceProvider;
use Megaads\DealsPage\Commands\MigrateExecution;
use Megaads\DealsPage\Commands\MigrationCreate;
use Megaads\DealsPage\Repositories\CatalogRepository;
use Megaads\DealsPage\Repositories\DealRepository;

class DealsPageProvider extends ServiceProvider
{
    protected $commands = [
        MigrationCreate::class,
        MigrateExecution::class,
    ];

    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            include dirname(__FILE__) . '/../Routes/web.php';
            include dirname(__FILE__) . '/../Routes/service.php';
        }

        if (file_exists(dirname(__FILE__) . '/../Helpers/helper.php')) {
            require dirname(__FILE__) . '/../Helpers/helper.php';
        }

        //Load Package views
        $this->loadViewsFrom(dirname(__FILE__) . '/../Resoures/Views/', 'deals-page');

        //Publish assests
        $this->publishAssets();

        //Publish config
        $this->publishConfig();

        //Registry singleton
//        $this->registrySingleton();
    }

    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * @return void
     */
    private function publishConfig()
    {
        if (function_exists('config_path')) {
            $path = dirname(__FILE__) . '/../../configs/deals-page.php';
            $this->publishes([$path => config_path('deals-page.php')], 'config');
        }
    }

    /**
     * @return void
     */
    protected function publishAssets() {
        $this->publishes([
            dirname(__FILE__) . '/../Assests/' => public_path('vendor/deals-page')
        ], 'assets');
    }

    /**
     * @return void
     */
    protected function registrySingleton() {
        $this->app->singleton('DealRepository', function($app) {
            return new DealRepository();
        });
        $this->app->singleton('CatalogRepository', function($app) {
            return new CatalogRepository();
        });
    }

    protected function registryCommand() {

    }

}