<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/28
 * Time: 11:49
 */

namespace Jasmine\library\cache\driver\interfaces;


interface DriverInterface
{

    function get($key);

    function set($key, $value, $expire = 0);

    function rm($key);

}