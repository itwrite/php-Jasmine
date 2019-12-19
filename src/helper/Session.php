<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/12/29
 * Time: 17:54
 */

namespace Jasmine\helper;

use Jasmine\util\Arr;

/**
 * relative Arr
 * Class Session
 * @package Jasmine\Common
 */
class Session
{

    /**
     * @param array $options
     * @return bool
     * itwri 2019/7/31 12:41
     */
    static public function start($options = ['read_and_close' => true])
    {
        if (!session_id()) {
            return session_start($options);
        }
        return true;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:07
     *
     * @param $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    static public function get($key, $default = null, $filter = null)
    {
        self::start();

        if (func_num_args() == 0 || is_null($key) || empty($key)) return $_SESSION;

        return Arr::get($_SESSION, $key, $default, $filter);
    }

    /**
     * @param $key
     * @param string $value
     */
    static public function set($key, $value = '')
    {
        self::start();
        //the key is null, do nothing
        if (is_null($key)) return;

        //the value is null, remove it
        if (is_null($value)) Arr::forget($_SESSION, $key);

        //the key is an array, all set into the config by foreach
        else if (is_array($key)) foreach ($key as $k => $v) {
            Arr::set($_SESSION, $k, $v);
        }

        //else all set into config anyway
        else Arr::set($_SESSION, $key, $value);
    }

    /**
     * @param $key
     */
    static public function forget($key)
    {
        self::start();
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (is_string($k)) {
                    self::set($v, null);
                }
            }
        } else if (is_string($key)) {
            self::set($key, null);
        }
    }

    /**
     * @return bool
     */
    static public function destroy()
    {
        session_unset();
        return session_destroy();
    }


    /**
     * 重新生成session_id
     * User: Peter
     * Date: 2019/3/21
     * Time: 15:38
     *
     * @param bool $delete  是否删除关联会话文件
     * @return bool
     */
    static public function regenerate($delete = false)
    {
       return session_regenerate_id($delete);
    }

    /**
     * 暂停session
     * @access public
     * @return void
     */
    static public function pause()
    {
        // 暂停session
        session_write_close();
    }
}