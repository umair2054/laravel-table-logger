<?php

namespace UmairHanif\LaravelTableLogger\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            $this->syncLogTableColumns($model->getTable(), $logTableName);

            if ($action === 'update') {
                $changes = $model->getChanges();

                // Define specific fields you want to log separately
                $specialTrackedFields = [
                    'password' => 'password_changed',
                    'email'    => 'email_changed',
                ];

                foreach ($specialTrackedFields as $field => $specialAction) {
                    if (array_key_exists($field, $changes)) {
                        $this->logChange($model, $logTableName, $specialAction);
                    }
                }

                // Always log the generic update once
                $this->logChange($model, $logTableName, 'update');
            } else {
                $this->logChange($model, $logTableName, $action);
            }
        }
    }

    /**
     * Convert table name to proper log table name
     */
    public function getProperLogTableName(string $tableName): string
    {
        $irregulars = config('table-logger.irregular_plurals', []);

        // Use irregulars if defined
        if (array_key_exists($tableName, $irregulars)) {
            return $irregulars[$tableName] . '_logs';
        }

        // Use Laravel's singular helper to get base model name
        $singularName = Str::singular($tableName);

        return $singularName . '_logs';
    }

    /**
     * Log the change to the log table
     */
    protected function logChange(Model $model, string $logTableName, string $action): void
    {
        $logData = $model->getAttributes();

        $logData['tbl_original_id'] = $logData['id'] ?? null;
        $logData['this_log_action'] = $action;
        $logData['this_log_ip'] = request()->ip();
        $logData['this_log_user_agent'] = request()->userAgent();
        $logData['this_log_url'] = request()->fullUrl();
        $logData['this_log_changed_at'] = now();
        $logData['this_log_modified_by'] = auth()->check() ? auth()->id() : null;

        unset($logData['id']);

        if ($action === 'update') {
            $changes = [];

            foreach ($model->getChanges() as $key => $newValue) {
                $original = $model->getOriginal($key);
                $changes[$key] = [
                    'old' => $original,
                    'new' => $newValue,
                ];
            }

            $logData['this_log_changes'] = json_encode($changes);
        } else {
            $logData['this_log_changes'] = null;
        }

        DB::table($logTableName)->insert($logData);
    }


    /**
     * Create a log table based on the original table structure
     */
    protected function createLogTable(string $originalTable, string $logTableName): void
    {
        $columns = DB::select("SHOW COLUMNS FROM `$originalTable`");

        $columnDefinitions = [];

        // Add a new auto-increment ID for the log table
        $columnDefinitions[] = "`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";

        foreach ($columns as $column) {
            // Skip original `id` column to avoid conflict
            if ($column->Field === 'id') {
                continue;
            }

            $columnDefinition = "`{$column->Field}` {$column->Type}";

            if ($column->Null === 'NO' && !isset($column->Default)) {
                $columnDefinition .= ' NOT NULL';
            } elseif ($column->Null === 'YES') {
                $columnDefinition .= ' NULL';
            }

            if (isset($column->Default)) {
                // Quote strings only
                $default = is_numeric($column->Default) ? $column->Default : "'{$column->Default}'";
                $columnDefinition .= " DEFAULT $default";
            }

            // Add extra flags like AUTO_INCREMENT if needed (but we skip those intentionally here)
            if ($column->Extra && stripos($column->Extra, 'auto_increment') !== false) {
                // skip auto_increment to avoid conflicts
            }

            $columnDefinitions[] = $columnDefinition;
        }

        // Add logging specific columns
        $columnDefinitions[] = '`tbl_original_id` BIGINT UNSIGNED NULL COMMENT "ID of the original record"';
        $columnDefinitions[] = '`this_log_action` VARCHAR(30) NOT NULL COMMENT "create/update/delete"';
        $columnDefinitions[] = '`this_log_ip` VARCHAR(45) NULL COMMENT "IP address of the user"';
        $columnDefinitions[] = '`this_log_user_agent` TEXT NULL COMMENT "User Agent info"';
        $columnDefinitions[] = '`this_log_url` TEXT NULL COMMENT "Request URL"';
        $columnDefinitions[] = '`this_log_modified_by` BIGINT UNSIGNED NULL COMMENT "Who made the change"';
        $columnDefinitions[] = '`this_log_changes` JSON NULL COMMENT "Old and new values when updated"';
        $columnDefinitions[] = '`this_log_changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';

        $createTableSql = "CREATE TABLE `$logTableName` (" . 
                        implode(', ', $columnDefinitions) . 
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        DB::statement($createTableSql);
    }

    protected function syncLogTableColumns(string $originalTable, string $logTableName): void
    {
        $originalColumns = DB::select("SHOW COLUMNS FROM `$originalTable`");
        $logColumns = DB::select("SHOW COLUMNS FROM `$logTableName`");

        $logColumnNames = array_map(fn($col) => $col->Field, $logColumns);

        foreach ($originalColumns as $column) {
            if ($column->Field === 'id') {
                continue; // skip primary ID
            }

            if (!in_array($column->Field, $logColumnNames)) {
                // Construct column definition
                $definition = "`{$column->Field}` {$column->Type}";

                if ($column->Null === 'NO' && !isset($column->Default)) {
                    $definition .= ' NOT NULL';
                } elseif ($column->Null === 'YES') {
                    $definition .= ' NULL';
                }

                if (isset($column->Default)) {
                    $default = is_numeric($column->Default) ? $column->Default : "'{$column->Default}'";
                    $definition .= " DEFAULT $default";
                }

                $alterSql = "ALTER TABLE `$logTableName` ADD $definition";
                DB::statement($alterSql);
            }
        }
    }

}