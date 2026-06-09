<?php

return [
    'driver' => env('ORACLE_DRIVER', 'oracle'),
    'host' => env('ORACLE_HOST', ''),
    'port' => env('ORACLE_PORT', '1521'),
    'database' => env('ORACLE_DATABASE', ''),
    'username' => env('ORACLE_USERNAME', ''),
    'password' => env('ORACLE_PASSWORD', ''),
    'charset' => env('ORACLE_CHARSET', 'AL32UTF8'),
    'prefix' => '',
];