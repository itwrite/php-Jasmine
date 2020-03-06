<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/3/6
 * Time: 22:28
 */

namespace Jasmine\library\cache\driver;


use Jasmine\library\cache\driver\interfaces\StoreInterface;

class NullStore implements StoreInterface
{

    protected $prefix = '';
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function get($key)
    {
        // TODO: Implement get() method.
        return null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array $keys
     * @return array
     */
    public function getMany(array $keys)
    {
        // TODO: Implement getMany() method.
        return [];
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed $value
     * @param  float|int $minutes
     * @return bool
     */
    public function put($key, $value, $minutes)
    {
        // TODO: Implement put() method.
        return false;
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array $values
     * @param  float|int $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {
        // TODO: Implement putMany() method.
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        // TODO: Implement increment() method.
        return 0;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        // TODO: Implement decrement() method.
        return 0;
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function forever($key, $value)
    {
        // TODO: Implement forever() method.
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function forget($key)
    {
        // TODO: Implement forget() method.
        return false;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        // TODO: Implement flush() method.
        return false;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}