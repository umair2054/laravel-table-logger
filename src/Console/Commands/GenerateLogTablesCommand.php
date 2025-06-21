<?php

namespace UmairHanif\LaravelTableLogger\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UmairHanif\LaravelTableLogger\Services\TableLoggerService;

class GenerateLogTablesCommand extends Command
{
    protected $signature = 'make:log-tables 
                            {--tables= : Comma-separated list of specific tables to process}
                            {--force : Overwrite existing log tables}';
    
    protected $description = 'Generate log tables for all existing database tables';

    public function handle(TableLoggerService $loggerService)
    {
        $specificTables = $this->option('tables') 
            ? explode(',', $this->option('tables')) 
            : null;

        $tables = $this->getTablesToProcess($specificTables);

        foreach ($tables as $table) {
            // $logTableName = $table . config('table-logger.log_table_suffix');
            $logTableName = $loggerService->getProperLogTableName($table);
            
            if (Schema::hasTable($logTableName) && !$this->option('force')) {
                $this->line("Skipping {$logTableName} - already exists (use --force to overwrite)");
                continue;
            }

            $this->info("Creating log table for {$table}...");
            
            try {
                $loggerService->createLogTable($table, $logTableName);
                $this->info("Created {$logTableName} successfully!");
            } catch (\Exception $e) {
                $this->error("Failed to create {$logTableName}: " . $e->getMessage());
            }
        }

        $this->info('Log table generation completed!');
    }

    protected function getTablesToProcess(?array $specificTables): array
    {
        if ($specificTables) {
            return $specificTables;
        }

        // Get all tables except migrations and log tables
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        
        return array_filter($tables, function ($table) {
            return !str_ends_with($table, config('table-logger.log_table_suffix')) &&
                   $table !== 'migrations';
        });
    }
}