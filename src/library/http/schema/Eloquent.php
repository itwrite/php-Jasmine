<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/11/15
 * Time: 18:15
 */

namespace Jasmine\library\http\schema;

use Jasmine\util\Arr;

abstract class Eloquent
{

    protected $data = [];

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 1:52
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return array|mixed
     */
    public function get($key = '', $default = null, $filter = null)
    {
        if (func_num_args() == 0) return self::all();

        $result = Arr::get($this->data, $key, $default);

        if ($filter) {
            if (is_callable($filter)) {
                return call_user_func($filter, $result);
            } elseif (is_string($filter)) {
                foreach (explode(',', $filter) as $fun) {
                    if (is_callable($fun)) {
                        $result = call_user_func($fun, $result);
                    }
                }
            } elseif (is_array($filter)) {
                foreach ($filter as $fun => $item) {
                    if (is_callable($fun)) {
                        $item = is_array($item) ? $item : [$item];
                        array_unshift($item, $result);
                        $result = call_user_func_array($fun, $item);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @param string $value
     * @return $this
     * itwri 2019/11/19 21:29
     */
    public function set($key, $value = '')
    {
        //the key is null, do nothing
        if (is_null($key)) return $this;

        //the value is null, remove it
        if (is_null($value)) Arr::forget($this->data, $key);

        //the key is an array, all set into the config by foreach
        else if (is_array($key)) foreach ($key as $k => $v) {
            Arr::set($this->data, $k, $v);
        }

        //else all set into config anyway
        else Arr::set($this->data, $key, $value);
        return $this;
    }

    /**
     * Extend is different from merge
     * Merge will overwrite the old value any way
     * Extend just set the same type of both values;
     * @param $key
     * @param string $value
     * @return $this
     * itwri 2019/11/19 21:29
     */
    public function extend($key, $value = '')
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
                Arr::add($this->data, $key, $value);
            }
        }
        return $this;
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/3/8
     * Time: 2:35
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }
}