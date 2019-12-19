<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 14:16
 */

namespace Jasmine\library\db\connection;

use Jasmine\library\db\connection\capsule\Link;
use Jasmine\library\db\connection\interfaces\ConnectionInterface;
use Jasmine\library\exception\ErrorException;

require_once 'capsule/Link.php';
require_once 'interfaces/ConnectionInterface.php';

class Connection implements ConnectionInterface
{
    protected $config = [];
    /**
     * 主连接
     * @var Link|null
     */
    protected $masterLink = null;

    /**
     * @var array
     */
    protected $extraLinks = [];

    /**
     * 只读连接
     * @var Link|null
     */
    protected $readLink = null;

    function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param $data
     * @param $config
     * @return $this
     * itwri 2019/12/19 13:06
     */
    protected function activateConfig($data, &$config)
    {
        /**
         * 拆分索引数组、key:value数组
         */
        $assocArr = [];
        $keysArr = [];
        foreach ($data as $key => $item) {
            if (is_numeric($key)) {
                $assocArr[] = $item;
            } elseif (is_string($key)) {
                $keysArr[$key] = $item;
            }
        }

        /**
         * 如果索引中的数据不为空
         */
        if ($len = count($assocArr) > 0) {
            $val = $assocArr[rand(0, $len - 1)];
            if (is_string($val)) {
                $config['host'] = $val;
            } elseif (is_array($val)) {

                $this->activateConfig($val, $config);
            }
        }

        /**
         * 如果key:value数组不为空
         * 且value是字符串，则更新配置
         */
        if (count($keysArr) > 0) {
            foreach ($keysArr as $key => $value) {
                if (is_string($value)) {
                    $config[$key] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * @param string $flag
     * @return array
     * itwri 2019/12/19 13:09
     */
    public function getConfig($flag = 'write')
    {
        $config = $this->config;
        /**
         * 如果有特别设置了
         */
        if (isset($config[$flag])) {

            if (is_array($config[$flag])) {

                $this->activateConfig($config[$flag], $config);

            } elseif (is_string($config[$flag])) {

                $config['host'] = $config[$flag];
            }
        }
        return $config;
    }

    /**
     * @param mixed $name
     * @return Link|mixed|null
     * @throws \Exception
     * itwri 2019/12/19 13:57
     */
    public function getLink($name = true)
    {
        if ($name === true || $name == 'write') {

            return $this->getMasterLink();
        }

        if ($name === false || $name == 'read') {
            return $this->getReadLink();
        }

        if (!is_null($name) && !is_bool($name)) {
            if (!isset($this->config[$name])) {
                throw new \Exception($name.' connection is not available.');
            }
            /**
             * 不存在则新建
             */
            if(!isset($this->extraLinks[$name])){
                $this->extraLinks[$name] = new Link($this->getConfig($name));
            }
            return $this->extraLinks[$name];
        }
        return $this->getReadLink();
    }

    /**
     * @return Link|null
     * itwri 2019/12/19 13:11
     */
    public function getMasterLink()
    {
        if (!($this->masterLink instanceof Link)) {
            $this->masterLink = new Link($this->getConfig('write'));
        }
        return $this->masterLink;
    }

    /**
     * @return Link|null
     * itwri 2019/12/19 13:13
     */
    public function getReadLink()
    {
        if (!($this->readLink instanceof Link)) {
            $this->readLink = new Link(isset($this->config['read']) ? $this->getConfig('read') : $this->getConfig('write'));
        }
        return $this->readLink;
    }
}