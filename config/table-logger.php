<?php

return [
    /*
     * Log table suffix
     */
    'log_table_suffix' => '_logs',

    /*
     * Log store pref
     */
    'driver' => env('TABLE_LOGGER_DRIVER', 'database'),

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
        'child'   => 'children',
        'person'  => 'people',
        'man'     => 'men',
        'woman'   => 'women',
        'mouse'   => 'mice',
        'goose'   => 'geese',
        'tooth'   => 'teeth',
        'foot'    => 'feet',
        'ox'      => 'oxen',
        'louse'   => 'lice',
        'die'     => 'dice',
        'criterion' => 'criteria',
        'phenomenon'=> 'phenomena',
        'index'   => 'indices', 
        'appendix'=> 'appendices',
        'cactus'  => 'cacti',
        'focus'   => 'foci',
        'fungus'  => 'fungi',
        'nucleus' => 'nuclei',
        'syllabus'=> 'syllabi',
        'analysis'=> 'analyses',
        'diagnosis'=> 'diagnoses',
        'thesis'  => 'theses',
        'basis'   => 'bases',
        'crisis'  => 'crises',
        'phenomenon'=>'phenomena',
    ],
];