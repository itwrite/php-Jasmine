<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/18
 * Time: 19:08
 */

return [
    'debug'=>false,
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */
    'connections'=>[
        'mysql'=>[
            'write'=>[
                'host'=> 'localhost'
            ],
            'read'=>[
                'dbname'=>'a2'
            ],
            'driver'=>'mysql',
            'host'=>'localhost',
            'dbname'=>'ant_shop',
            'port'=>'3306',
            'username'=>'root',
            'password'=>'root',
            'table_prefix'=>'',
            'sticky'=>true,
            'debug'=>false
        ],

    ],

];
