<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/28
 * Time: 12:06
 */

namespace Jasmine\library\cache\interfaces;


interface CacheInterface
{

    function get($key);
    function set($key,$value);
    function store($key,$value);
}