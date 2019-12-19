<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/12/31
 * Time: 12:24
 */

namespace Jasmine\helper;

use Jasmine\util\Arr;

class Server
{
    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:07
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    static public function get($key = '', $default = null, $filter = null)
    {
        if (func_num_args() == 0 || is_null($key)) return $_SERVER;

        return Arr::get($_SERVER, $key, $default, $filter);
    }

    /**
     * @return array
     */
    static public function all()
    {
        return $_SERVER;
    }

    /**
     * @return string
     */
    static public function domain()
    {
        return strtolower($_SERVER['SERVER_NAME']);
    }

    /**
     * @return string
     */
    static public function scheme()
    {
        return $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    }

    /**
     * @return string
     */
    static public function port()
    {
        return ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : ':' . $_SERVER['SERVER_PORT'];
    }

    /**
     * @return string
     */
    static public function root()
    {
        return self::scheme() . self::domain() . self::port() . '/';
    }

    /**
     * @return string
     */
    static public function baseUrl()
    {
        //explode the script path
        $arr = explode('/', self::get('PHP_SELF', ''));
        //the last one is the filename,so remove it;
        array_pop($arr);
        //return the base url
        return self::root() . implode('/', $arr);
    }
}