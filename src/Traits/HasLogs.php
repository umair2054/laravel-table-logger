<?php

namespace UmairHanif\LaravelTableLogger\Traits;

use Illuminate\Support\Facades\DB;
use UmairHanif\LaravelTableLogger\Services\TableLoggerService;
use Illuminate\Support\Facades\Schema;
trait HasLogs
{
    /**
     * Get logs for a single model instance
     */
    public function logs()
    {
        $table = $this->getTable();
        $logTable = TableLoggerService::getProperLogTableName($table);

        if (!Schema::hasTable($logTable)) {
            return collect();
        }

        return DB::table($logTable)->where('tbl_original_id', $this->id);
    }

    /**
     * Get logs for a collection of models
     *
     * @param \Illuminate\Support\Collection $models
     * @return \Illuminate\Support\Collection
     */
    public static function logsBatch($models)
    {
        if ($models->isEmpty()) {
            return collect();
        }

        $firstModel = $models->first();
        $table = $firstModel->getTable();
        $logTable = TableLoggerService::getProperLogTableName($table);

        if (!Schema::hasTable($logTable)) {
            return collect();
        }

        $ids = $models->pluck('id')->unique()->toArray();

        return DB::table($logTable)->whereIn('tbl_original_id', $ids);
    }
}
