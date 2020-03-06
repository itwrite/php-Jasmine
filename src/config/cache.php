<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/12
 * Time: 16:48
 */

return [
    /*
     |--------------------------------------------------------------------------
     | Default the driver of Cache
     |--------------------------------------------------------------------------
     |
     | Here you may specify which of the drivers below you wish
     | to use as your default driver for all Cache work.
     |
     */
    'driver'=>\Jasmine\library\cache\driver\FileStore::class,

    /*
     |--------------------------------------------------------------------------
     | The directory of cache
     |--------------------------------------------------------------------------
     |
     | Here you may specify which of the directories below you wish to use as your default cache directory.
     */
    'directory'=>\Jasmine\helper\Config::get('PATH_RUNTIME').'/cache',
];