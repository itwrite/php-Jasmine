<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/28
 * Time: 11:09
 */

namespace Jasmine\library\cache\driver;


use Jasmine\library\cache\driver\interfaces\DriverInterface;
use \Jasmine\library\file\File as FileHelper;

class File implements DriverInterface
{
    protected $rootPath = '';

    function __construct($options)
    {
        $this->rootPath = isset($options['root_path']) ? $options['root_path'] : '';
        /**
         * 初始化目录
         */
        $this->rootPath && !is_dir($this->rootPath) && mkdir($this->rootPath, 0755, true);
    }

    /**
     * @param $key
     * @return string
     * itwri 2019/8/28 14:11
     */
    protected function getFilePath($key)
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . '/' . $key;
    }

    /**
     * @param $key
     * @return null
     * @throws \ErrorException
     * itwri 2020/2/29 11:11
     */
    function get($key)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            /**
             * 加锁读取
             */
            $content = FileHelper::init()->get($file,1);
            $data = unserialize($content);
            if(!isset($data['value'])){
                return null;
            }

            if ($data['expire_time'] != 0 && $data['expire_time'] < time()) {
                $this->rm($key);
                return null;
            }
            return $data['value'];
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire
     * @return bool|int
     * itwri 2019/8/28 13:39
     */
    function set($key, $value, $expire = 0)
    {
        $file = $this->getFilePath($key);

        $expire_time = $expire != 0 ? time() + $expire : 0;

        $content = serialize(['expire_time' => $expire_time, 'value' => $value]);

        /**
         * 加锁写入
         */
        return FileHelper::init()->put($file, $content, 1);
    }

    /**
     * @param $key
     * @return bool
     * itwri 2019/8/28 13:28
     */
    function rm($key)
    {
        $file = $this->getFilePath($key);

        return FileHelper::init()->delete($file);
    }
}