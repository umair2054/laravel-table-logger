<?php

namespace UmairHanif\LaravelTableLogger\Traits;

use UmairHanif\LaravelTableLogger\Services\TableLoggerService;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            app(TableLoggerService::class)->handleModelChanges($model, 'create');
        });

        static::updated(function ($model) {
            app(TableLoggerService::class)->handleModelChanges($model, 'update');
        });

        static::deleted(function ($model) {
            app(TableLoggerService::class)->handleModelChanges($model, 'delete');
        });
    }

    /**
     * Get the log table name for this model
     */
    public function getLogTableName(): string
    {
        return app(TableLoggerService::class)
            ->getProperLogTableName($this->getTable());
    }
}