<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/28
 * Time: 11:04
 */

namespace Jasmine\library\cache;

use Jasmine\library\cache\interfaces\CacheInterface;
use Jasmine\library\cache\interfaces\DriverInterface;

class Cache implements CacheInterface
{
    protected $data = [];
    /**
     * @var  DriverInterface
     */
    protected $handler = null;

    function __construct($type, $options = [])
    {
        $type = ucfirst($type);
        $class = "Jasmine\library\cache\driver\\{$type}";
        $this->handler = new $class($options);
    }

    /**
     * @return DriverInterface
     * itwri 2019/8/28 12:04
     */
    function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param $name
     * @return string
     * itwri 2019/8/28 12:33
     */
    function getCacheKey($name)
    {
        return md5($name);
    }

    /**
     * @param $name
     * @return mixed
     * itwri 2019/8/28 12:35
     */
    function get($name)
    {
        $key = $this->getCacheKey($name);

        return isset($this->data[$key]) ? $this->data[$key] : $this->getHandler()->get($key);
    }

    /**
     * @param $name
     * @param $key
     * @return null
     * itwri 2019/8/28 14:14
     */
    function mGet($name, $key)
    {
        $data = $this->get($name);
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire seconds
     * @return mixed
     * itwri 2019/8/28 13:21
     */
    function set($name, $value, $expire = 0)
    {
        $key = $this->getCacheKey($name);

        $this->data[$key] = $value;

        return $this->getHandler()->set($key, $value, $expire);
    }

    /**
     * @param $name
     * @param $key
     * @param null $value
     * @param int $set_expire
     * @return mixed
     * itwri 2019/8/28 14:00
     */
    function mSet($name, $key, $value = null, $set_expire = 0)
    {
        $data = $this->get($name);
        $data = is_array($data) ? $data : [];
        if (is_array($key)) {
            $set_expire = (int)$value;
            foreach ($key as $k => $v) {
                $data[$k] = $v;
            }
        } else {
            $data[$key] = $value;
        }
        return $this->set($name, $data, $set_expire);
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @return mixed
     * itwri 2019/8/28 14:13
     */
    function store($name, $value, $expire = 0)
    {
        return $this->set($name, $value, $expire);
    }
}