<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/12/25
 * Time: 17:12
 */

namespace Jasmine\helper;

use Jasmine\library\file\File;
use Jasmine\util\Arr;

require_once __DIR__.'/../util/Arr.php';

class Config
{
    /**
     * @var array
     */
    static private $_config = array();

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    static public function get($key = '', $default = null)
    {
        if (func_num_args() < 1 || is_null($key)) return self::all();

        return Arr::get(self::$_config, $key, $default);
    }

    /**
     * @param $key
     * @param string $value
     */
    static public function set($key, $value = '')
    {
        //the key is null, do nothing
        if (is_null($key)) return;

        //the value is null, remove it
        if (is_null($value)) Arr::forget(self::$_config, $key);

        //the key is an array, all set into the config by foreach
        else if (is_array($key)) foreach ($key as $k => $v) {
            Arr::set(self::$_config, $k, $v);
        }

        //else all set into config anyway
        else Arr::set(self::$_config, $key, $value);
    }

    /**
     * Extend is different from merge
     * Merge will overwrite the old value any way
     * Extend just set the same type of both values;
     * @param $key
     * @param $value
     */
    static public function extend($key, $value = '')
    {
        if (is_array($key)) {

            foreach ($key as $k => $v) {
                self::extend($k, $v);
            }
        } elseif (is_string($key) || is_numeric($key)) {
            $val = self::get($key);
            if (is_array($val) && is_array($value)) {
                $new = Arr::extend($val, $value);
                self::set($key, $new);
            } elseif (gettype($val) == gettype($value)) {
                self::set($key, $value);
            } else {
                Arr::add(self::$_config, $key, $value);
            }
        }
    }

    /**
     * get all configure
     * @return array
     */
    static public function all()
    {
        return self::$_config;
    }

    /**
     * @param $file
     */
    static public function load($file)
    {
        if (is_dir($file)) {
            $files = File::init()->files($file);
            foreach ($files as $f) {
                if(is_file($f)){
                    self::load($f);
                }
            }
        } else if (is_file($file)) {
            $arr = pathinfo($file);
            $filename = $arr['filename'];
            $config = @include($file);
            Config::extend($filename, $config);
        }
    }

    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if (is_callable("self::" . $name)) {
            call_user_func_array("self::" . $name, $arguments);
        }
    }
}