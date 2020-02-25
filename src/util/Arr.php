<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/12/26
 * Time: 14:46
 */

namespace Jasmine\util;


/**
 * Class Arr
 * @package Jasmine\library\util
 */
class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     * User: Peter
     * Date: 2019/3/21
     * Time: 11:12
     *
     * @param $target
     * @param $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    static public function get($target, $key, $default = null, $filter = null)
    {
        if (func_num_args() < 2 || is_null($key)) return $target;

        if (is_string($key)) {

            if (is_array($target) && isset($target[$key])) return self::filterValue($target[$key], $filter);

            foreach (explode('.', $key) as $segment) {
                if (is_array($target)) {
                    if (!array_key_exists($segment, $target)) {
                        return self::filterValue(self::value($default), $filter);
                    }

                    $target = $target[$segment];
                } elseif (is_object($target)) {
                    if (!isset($target->{$segment})) {
                        return self::filterValue(self::value($default), $filter);
                    }

                    $target = $target->{$segment};
                } else {
                    return self::filterValue(self::value($default), $filter);
                }
            }
        }

        return self::filterValue($target, $filter);
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    static public function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 11:12
     *
     * @param $data
     * @param $filter
     * @return mixed
     */
    static public function filterValue($data, $filter = null)
    {
        if ($filter) {
            if (is_callable($filter)) {
                return call_user_func($filter, $data);
            } elseif (is_string($filter)) {
                foreach (explode(',', $filter) as $fun) {
                    if (is_callable($fun)) {
                        $data = call_user_func($fun, $data);
                    }
                }
            } elseif (is_array($filter)) {
                foreach ($filter as $fun => $item) {
                    if (is_callable($fun)) {
                        $item = is_array($item) ? $item : [$item];
                        array_unshift($item, $data);
                        $data = call_user_func_array($fun, $item);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    static public function add(&$array, $key, $value)
    {
        if (is_null(self::get($array, $key))) {
            self::set($array, $key, $value);
        }
        return $array;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    static public function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Remove an array item from a given array using "dot" notation.
     * @param $array
     * @param $key
     * @return void
     */
    static public function forget(&$array, $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
        return;
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array $array
     * @param  array|string $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    static public function pull(&$array, $key, $default = null)
    {
        $value = self::get($array, $key, $default);

        self::forget($array, $key);

        return $value;
    }

    /**
     * @param array $array1
     * @param array $array2,...
     * @return array
     */
    static public function extend(array $array1, array $array2 = null)
    {
        /**
         * get all arguments
         */
        $args = func_get_args();

        /**
         * if there are only two args,then extend the second array to the first one.
         */
        if (count($args) == 2) {
            foreach ($array2 as $key => $value) {
                if (isset($array1[$key])) {
                    /**
                     * key 分两种情况:string,int
                     */
                    if (is_numeric($key)) {
                        /**
                         * 如果array2中的$key是数字index,
                         * 虽然在array1中存在对应的值,但值不同，则新增,
                         * 反之跳过
                         */
                        if ($array1[$key] !== $value) {
                            $array1[] = self::value($value);
                        }
                    } /**
                     * 只有类型相同才可以
                     */
                    elseif (gettype($array1[$key]) == gettype($value)) {
                        /**
                         * 如果是数组
                         */
                        $array1[$key] = is_array($array1[$key]) ? self::extend($array1[$key], $value) : self::value($value);
                    }
                } else {
                    $array1[$key] = self::value($value);
                }
            }
            return $array1;
        } else {
            $array1 = array_shift($args);
            foreach ($args as $arr) {
                $array1 = self::extend($array1, $arr);
            }
        }
        return $array1;
    }

    /**
     * @param $value
     * @return mixed
     */
    static public function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * @param $array
     * @return bool
     * itwri 2020/1/7 11:21
     */
    static public function isAssoc($array)
    {
        if (is_array($array)) {
            $keys = array_keys($array);
            return self::identicalValues($keys,array_keys($keys));
        }
        return false;
    }

    /**
     * 判断两个数组的值是否相同
     * @param $arrayA
     * @param $arrayB
     * @return bool
     * itwri 2019/12/3 15:30
     */
    static public function identicalValues( $arrayA , $arrayB ) {

        sort( $arrayA );
        sort( $arrayB );

        return $arrayA == $arrayB;
    }

    /**
     * 将列表转成树形
     * @param array $list
     * @param int $parent_id
     * @param string $parent_key
     * @return array
     * itwri 2020/1/7 11:27
     */
    static public function toTree(array $list, $parent_id = 0, $parent_key = 'parent_id')
    {
        $result = [];
        if (self::isAssoc($list)) {
            // 找出子项 和 剩下项
            $res1 = self::findChildren($list, $parent_id, $parent_key);

            if (!empty($res1['children'])) {
                foreach ($res1['children'] as $child) {

                    $children = self::toTree($res1['remain'], $child['id'], $parent_key);
                    if (!empty($children)) {
                        $child['children'] = $children;
                    }

                    $result[] = $child;
                }
            }

        }
        return $result;
    }

    /**
     * 找出子项 和 剩下项
     * @param $data
     * @param $parent_id
     * @param string $parent_key
     * @return array
     * itwri 2020/1/7 11:27
     */
    static public function findChildren($data, $parent_id, $parent_key = 'parent_id')
    {
        $result = ['children' => [], 'remain' => []];
        if (self::isAssoc($data)) {
            foreach ($data as $datum) {
                if (isset($datum[$parent_key]) && $datum[$parent_key] == $parent_id) {
                    $result['children'][] = $datum;
                } else {
                    $result['remain'][] = $datum;
                }
            }
        }
        return $result;
    }

    /**
     * @param $config
     * @return array
     */
    static public function parseIni($config)
    {
        if (is_file($config)) {
            return parse_ini_file($config, true);
        } else {
            return parse_ini_string($config, true);
        }
    }

    /**
     * @param $json
     * @return mixed
     */
    static public function parseJson($json)
    {
        {
            if (is_file($json)) {
                $json = file_get_contents($json);
            }
            return json_decode($json, true);
        }
    }

    /**
     * @param $config
     * @return array
     */
    static public function parseXml($config)
    {
        if (is_file($config)) {
            $content = simplexml_load_file($config);
        } else {
            $content = simplexml_load_string($config);
        }
        $result = (array)$content;
        foreach ($result as $key => $val) {
            if (is_object($val)) {
                $result[$key] = (array)$val;
            }
        }
        return $result;
    }

    /**
     * @param $name
     * @param $arguments
     * itwri 2020/1/7 11:17
     */
    function __call($name, $arguments)
    {
        if (is_callable("self::" . $name)) {
            call_user_func_array("self::" . $name, $arguments);
        }
    }
}