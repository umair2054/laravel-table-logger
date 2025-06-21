<?php

namespace UmairHanif\LaravelTableLogger\Facades;

use Illuminate\Support\Facades\Facade;

class TableLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'table-logger';
    }
}