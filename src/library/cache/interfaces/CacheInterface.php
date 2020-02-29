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

    /**
     * 根据 key 获取缓存
     * @param $key
     * @return mixed
     * itwri 2020/2/29 0:16
     */
   public function get($key);

    /**
     * @param $name
     * @param $key
     * @return mixed
     * itwri 2020/2/29 0:17
     */
    public function mGet($name, $key);

    /**
     * @param $key
     * @param $value
     * @return mixed
     * itwri 2020/2/29 0:17
     */
    public function set($key, $value);

    /**
     * @param $name
     * @param $key
     * @param $value
     * @return mixed
     * itwri 2020/2/29 0:17
     */
    public function mSet($name, $key, $value);

    /**
     * @param $key
     * @param $value
     * @return mixed
     * itwri 2020/2/29 0:17
     */
    public function store($key, $value);

    /**
     * @param $name
     * @return mixed
     * itwri 2020/2/29 0:20
     */
    public function delete($name);
}