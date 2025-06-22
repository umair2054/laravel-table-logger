<?php

namespace UmairHanif\LaravelTableLogger\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use UmairHanif\LaravelTableLogger\Services\TableLoggerService;

class LoggerLog
{
    /**
     * Log a model action (create, update, delete) using table name and record ID.
     *
     * @param string $tableName  Table name (e.g. 'users')
     * @param int $id            Record ID
     * @param string $action     Action type: 'create', 'update', 'delete'
     * @return bool
     */
    public static function logAction(string $tableName, int $id, string $action): bool
    {
        $record = DB::table($tableName)->where('id', $id)->first();

        if (!$record) {
            return false;
        }

        // Build an instance of a generic Eloquent model manually
        $model = new class extends \Illuminate\Database\Eloquent\Model {
            protected $guarded = [];
            public $timestamps = false;
        };

        $model->setTable($tableName);
        $model->exists = true;

        foreach ((array) $record as $key => $value) {
            $model->setAttribute($key, $value);
        }

        app(\UmairHanif\LaravelTableLogger\Services\TableLoggerService::class)->handleModelChanges($model, $action);

        return true;
    }

}
