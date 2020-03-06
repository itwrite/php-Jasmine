<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/3/5
 * Time: 11:46
 */

namespace Jasmine\library\cache\driver;


use Jasmine\library\cache\driver\interfaces\StoreInterface;
use Jasmine\library\file\File;

class FileStore implements StoreInterface
{

    protected $prefix = '';

    protected $File;

    protected $directory;

    function __construct($options)
    {
        $this->File = new File();
        $this->directory = isset($options['directory']) ? $options['directory'] : '';
    }


    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->getPayload($key)['data'] ?: null;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  float|int  $minutes
     * @return bool
     */
    public function put($key, $value, $minutes)
    {
        $this->ensureCacheDirectoryExists($path = $this->path($key));

        $this->File->put(
            $path, $this->expiration($minutes).'#'.serialize($value), true
        );
        return true;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMany(array $keys)
    {
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }

        return $return;
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  float|int  $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $minutes);
        }
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $raw = $this->getPayload($key);
        $newValue = ((int) $raw['data']) + $value;
        $this->put($key, $newValue, $raw['time'] ?? 0);
        return $newValue;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        if ($this->File->exists($file = $this->path($key))) {
            return $this->File->delete($file);
        }

        return false;
    }


    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        if (! $this->File->isDirectory($this->directory)) {
            return false;
        }

        foreach ($this->File->directories($this->directory) as $directory) {
            if (! $this->File->deleteDirectory($directory)) {
                return false;
            }
        }

        return true;
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

    /**
     * Get the Filesystem instance.
     *
     * @return File
     */
    public function getFilesystem()
    {
        return $this->File;
    }

    /**
     * Get the working directory of the cache.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param  float|int  $minutes
     * @return int
     */
    protected function expiration($minutes)
    {
        $seconds = (int) ($minutes * 60);
        $time = strtotime("+{$seconds} seconds");

        return $minutes === 0 || $time > 9999999999 ? 9999999999 : (int) $time;
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureCacheDirectoryExists($path)
    {
        if (! $this->File->exists(dirname($path))) {
            $this->File->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param  string  $key
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

        return $this->directory.'/'.implode('/', $parts).'/'.$hash;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param  string  $key
     * @return array
     */
    protected function getPayload($key)
    {
        $path = $this->path($key);

        // If the file doesn't exist, we obviously cannot return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        try {
            $contents = $this->File->get($path, true);
            $expireTimeLength = strpos($contents,'#');
            $expire = substr($contents, 0, $expireTimeLength);
        } catch (\Exception $e) {
            return $this->emptyPayload();
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if ($this->currentTime() >= $expire) {
            $this->forget($key);

            return $this->emptyPayload();
        }

        /**
         * there is a letter '#' after expire time
         * so the start position add 1
         */
        $data = unserialize(substr($contents, $expireTimeLength+1));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on this cache on a later operation.
        $time = ($expire - $this->currentTime()) / 60;

        return compact('data', 'time');
    }

    /**
     * Get a default empty payload for the cache.
     *
     * @return array
     */
    protected function emptyPayload()
    {
        return ['data' => null, 'time' => null];
    }

    /**
     * @return int
     * itwri 2020/3/5 12:07
     */
    protected function currentTime(){
        return time();
    }
}