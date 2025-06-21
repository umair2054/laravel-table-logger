<?php

namespace UmairHanif\LaravelTableLogger\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableLoggerService
{
    /**
     * Handle model changes and log them
     */
    public function handleModelChanges(Model $model, string $action): void
    {
        if (!config('table-logger.enabled')) {
            return;
        }

        $logTableName = $this->getProperLogTableName($model->getTable());

        if (config('table-logger.auto_create_log_tables') && !Schema::hasTable($logTableName)) {
            $this->createLogTable($model->getTable(), $logTableName);
        }

        if (Schema::hasTable($logTableName)) {
            $this->logChange($model, $logTableName, $action);
        }
    }

    /**
     * Convert table name to proper log table name
     */
    public function getProperLogTableName(string $tableName): string
    {
        $irregulars = config('table-logger.irregular_plurals', []);

        // Check for irregular plurals first
        if (array_key_exists($tableName, $irregulars)) {
            return $irregulars[$tableName] . '_logs';
        }

        // Handle standard pluralization (remove trailing 's' or 'es')
        if (str_ends_with($tableName, 'es')) {
            return substr($tableName, 0, -2) . '_logs';
        }

        if (str_ends_with($tableName, 's')) {
            return substr($tableName, 0, -1) . '_logs';
        }

        // Default case (table doesn't end with 's')
        return $tableName . '_logs';
    }    

    /**
     * Log the change to the log table
     */
    protected function logChange(Model $model, string $logTableName, string $action): void
    {
        $logData = $model->getAttributes();
        $logData['original_id'] = $logData['id'] ?? null;
        $logData['action'] = $action;
        $logData['changed_at'] = now();

        unset($logData['id']);

        DB::table($logTableName)->insert($logData);
    }

    /**
     * Create a log table based on the original table structure
     */
    protected function createLogTable(string $originalTable, string $logTableName): void
    {
        $columns = DB::select("SHOW COLUMNS FROM $originalTable");

        $columnDefinitions = [];
        foreach ($columns as $column) {
            $columnDefinition = "`{$column->Field}` {$column->Type}";

            if ($column->Null === 'NO' && !isset($column->Default)) {
                $columnDefinition .= ' NOT NULL';
            } elseif ($column->Null === 'YES') {
                $columnDefinition .= ' NULL';
            }

            if (isset($column->Default)) {
                $columnDefinition .= " DEFAULT '{$column->Default}'";
            }

            $columnDefinitions[] = $columnDefinition;
        }

        // Add logging specific columns
        $columnDefinitions[] = '`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columnDefinitions[] = '`original_id` BIGINT UNSIGNED NULL';
        $columnDefinitions[] = '`action` VARCHAR(10) NOT NULL';
        $columnDefinitions[] = '`changed_at` DATETIME NOT NULL';
        $columnDefinitions[] = '`user_id` BIGINT UNSIGNED NULL COMMENT "Who made the change"';

        $createTableSql = "CREATE TABLE `$logTableName` (" . 
                         implode(', ', $columnDefinitions) . 
                         ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        DB::statement($createTableSql);
    }
}