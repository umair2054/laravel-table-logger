<?php

namespace UmairHanif\LaravelTableLogger;

use Illuminate\Support\ServiceProvider;
use UmairHanif\LaravelTableLogger\Services\TableLoggerService;

class LaravelTableLoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/table-logger.php', 'table-logger'
        );
        
        $this->app->singleton('table-logger', function ($app) {
            return new TableLoggerService();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/table-logger.php' => config_path('table-logger.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \UmairHanif\LaravelTableLogger\Console\Commands\GenerateLogTablesCommand::class,
            ]);
        }
    }
    
}