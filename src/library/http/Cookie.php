<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/21
 * Time: 12:09
 */

namespace Jasmine\library\http;


use Jasmine\util\Arr;

class Cookie
{

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:27
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function get($key = '', $default = null, $filter = null)
    {
        if (func_num_args() == 0 || empty($key)) {
            return $_COOKIE;
        }
        return Arr::get($_COOKIE, $key, $default, $filter);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:24
     *
     * @param $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    function set($name, $value = "", $expire = 0, $path = "/", $domain = "", $secure = false, $httponly = false)
    {
        return call_user_func_array('setcookie', func_get_args());
    }
}