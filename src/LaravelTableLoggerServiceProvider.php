<?php

namespace UmairHanif\LaravelTableLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UmairHanif\LaravelTableLogger\Services\TableLoggerService;

class LaravelTableLoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/table-logger.php', 'table-logger'
        );

        // Register singleton for TableLoggerService
        $this->app->singleton('table-logger', function ($app) {
            return new TableLoggerService();
        });
    }

    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/table-logger.php' => config_path('table-logger.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \UmairHanif\LaravelTableLogger\Console\Commands\GenerateLogTablesCommand::class,
            ]);
        }

        // Add logsBatch macro to Collection
        Collection::macro('logsBatch', function () {
            /** @var \Illuminate\Support\Collection $this */
            if ($this->isEmpty()) {
                return collect();
            }

            $firstModel = $this->first();
            if (!method_exists($firstModel, 'getTable')) {
                return collect();
            }

            $table = $firstModel->getTable();
            $logTable = TableLoggerService::getProperLogTableName($table);

            if (!Schema::hasTable($logTable)) {
                return collect();
            }

            $ids = $this->pluck('id')->unique()->toArray();
            return DB::table($logTable)->whereIn('tbl_original_id', $ids);
        });
    }
}
