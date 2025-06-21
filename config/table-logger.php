<?php

return [
    /*
     * Log table suffix
     */
    'log_table_suffix' => '_logs',

    /*
     * Enable/disable logging
     */
    'enabled' => env('TABLE_LOGGER_ENABLED', true),

    /*
     * Whether to automatically create log tables when they don't exist
     */
    'auto_create_log_tables' => true,

    /*
     * Irregular plural to singular mappings
     */
    'irregular_plurals' => [
        'people' => 'person',
        'children' => 'child',
        'men' => 'man',
        'women' => 'woman',
        // Add more as needed
    ],
];