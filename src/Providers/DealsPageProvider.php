<?php

namespace Megaads\DealsPage\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\ServiceProvider;
use Megaads\DealsPage\Commands\MigrateExecution;
use Megaads\DealsPage\Commands\MigrationCreate;
use Megaads\DealsPage\Middlewares\DealPageAuth;
use Megaads\DealsPage\Middlewares\DealPageCors;
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

        //Regitry queue callback
        $this->afterQueueDone();

        //Registry alias middleware
        $this->registerAliasMiddleware('deals_auth', 'Megaads\DealsPage\Middlewares\DealPageAuth');
        $kernel = $this->app->make(Kernel::class);

        // When the HandleCors middleware is not attached globally, add the PreflightCheck
        if (class_exists(\Barryvdh\Cors\HandleCors::class)) {
            $this->registerAliasMiddleware('deals_cors', 'Barryvdh\Cors\HandleCors');
        } else {
            $this->registerAliasMiddleware('deals_cors', 'Megaads\DealsPage\Middlewares\DealPageCors');
        }
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

    protected function afterQueueDone() {
        \Queue::after(function(JobProcessed $event) {
//            \Log::info('Job: ', [$event->job]);
//            \Log::info('Data: ', [$event->data["data"]] );
        });
    }

    protected function registerAliasMiddleware($alias, $class) {
        $appVersion = app()->version();
        preg_match('/\d+\.\d+/i', $appVersion, $matched);
        $matchVersion = isset($matched[0]) ? $matched[0] : 0;
        if ($matchVersion <= 5.2) {
            app('router')->middleware($alias, $class);
        } else if ($matchVersion > 5.2 && $matchVersion <= 5.8) {
            $this->app['router']->middleware($alias, $class);
        }
    }

    protected function registerCommonMiddleware($middleware) {
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware($middleware);
    }
}