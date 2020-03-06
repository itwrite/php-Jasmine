<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/3/4
 * Time: 0:50
 */

namespace Jasmine\helper;


use Jasmine\App;

class Cache
{

    /**
     * @param $name
     * @return mixed
     * itwri 2020/3/4 0:51
     */
    public static function get($name){
        return App::init()->getCache()->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @return mixed
     * itwri 2020/3/6 22:56
     */
    public static function set($name,$value,$expire = 0){
        return App::init()->getCache()->set($name,$value,$expire);
    }
}