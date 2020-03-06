<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/28
 * Time: 11:04
 */

namespace Jasmine\library\cache;

use Jasmine\library\cache\driver\FileStore;
use Jasmine\library\cache\driver\interfaces\StoreInterface;
use Jasmine\library\cache\interfaces\CacheInterface;

class Cache implements CacheInterface
{
    protected $data = [];

    protected $tag = '';

    /**
     * @var null | StoreInterface
     */
    protected $handler = null;

    function __construct($options = [])
    {
        $driver = isset($options['driver']) ? $options['driver'] : FileStore::class;
        $this->handler = new $driver($options);
    }

    /**
     * @return StoreInterface
     * itwri 2019/8/28 12:04
     */
    protected function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param $name
     * @return string
     * itwri 2019/8/28 12:33
     */
   protected function parseNameToKey($name)
    {
        return $name;
    }

    /**
     * @param $name
     * @return mixed
     * itwri 2019/8/28 12:35
     */
    function get($name)
    {
        $key = $this->parseNameToKey($name);

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
        $key = $this->parseNameToKey($name);

        $this->data[$key] = $value;

        return $this->getHandler()->put($key, $value, $expire / 60);
    }

    /**
     * @param $name
     * @param $key
     * @param null $value
     * @param int $expire
     * @return mixed
     * itwri 2019/8/28 14:00
     */
    function mSet($name, $key, $value = null, $expire = 0)
    {
        $data = $this->get($name);
        $data = is_array($data) ? $data : [];
        if (is_array($key)) {
            $expire = (int)$value;
            foreach ($key as $k => $v) {
                $data[$k] = $v;
            }
        } else {
            $data[$key] = $value;
        }
        return $this->set($name, $data, $expire);
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

    /**
     * @param $name
     * @return bool|mixed
     * itwri 2020/3/6 22:24
     */
    function delete($name){
        //
        $key = $this->parseNameToKey($name);

        //
        $res = $this->getHandler()->forget($key);
        if($res && isset($this->data[$key])){
            unset($this->data[$key]);
        }
        return $res;
    }
}