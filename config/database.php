<?php

return [

   'default' => 'accounts',

   'connections' => [
        'mysqlEpin' => [
            'driver'    => 'mysql',
            'host'      => env('EPIN_DB_HOST'),
            'port'      => env('EPIN_DB_PORT'),
            'database'  => env('EPIN_DB_DATABASE'),
            'username'  => env('EPIN_DB_USERNAME'),
            'password'  => env('EPIN_DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
         ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST'),
            'port'      => env('DB_PORT'),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],
    ],
];