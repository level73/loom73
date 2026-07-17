<?php

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'mysql' => [
            'driver' => env('DBTYPE', 'mysql'),
            'host' => env('DBHOST', '127.0.0.1'),
            'port' => env('DBPORT', '3306'),
            'database' => env('DBNAME', ''),
            'username' => env('DBUSER', ''),
            'password' => env('DBPASS', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'timezone' => env('DB_TIMEZONE', '+02:00'),
        ],

    ],

];