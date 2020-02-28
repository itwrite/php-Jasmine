<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2018/12/28
 * Time: 10:29
 */

namespace Jasmine\library\console;

use Jasmine\util\Arr;

class Input
{
    /**
     * @var mixed|string
     */
    protected $script = '';

    /**
     * @var mixed|string
     */
    protected $module = '';

    /**
     * @var mixed|string
     */
    protected $controller = '';

    /**
     * @var mixed|string
     */
    protected $action = '';

    /**
     * @var array
     */
    protected $commands = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * Input constructor.
     */
    function __construct()
    {
        $argv = $_SERVER['argv'];

        $this->script = array_shift($argv);

        $route = array_shift($argv);
        if ($route && preg_match('/^\/+(.*)/', $route, $mts)) {
            $route = $mts[1];
        }

        $arr = explode('/', $route);
        $this->module = array_shift($arr);
        $this->controller = array_shift($arr);
        $this->action = array_shift($arr);
        $argv[] = implode('/',$arr);

        $this->parse($argv);

    }

    /**
     * @return mixed|string
     * itwri 2020/2/27 12:49
     */
    function getScript(){
        return $this->script;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/20
     * Time: 14:30
     *
     * @param $argv
     */
    function parse($argv){
        $count = count($argv);
        /**
         * 第二个是执行文件，所以过滤掉，从1开始
         */
        for ($i = 0; $i < $count; $i++) {
            $item = $argv[$i];
            if (preg_match('/^\-([a-zA-Z]+)$/', $item, $matchData)) {
                $key = $matchData[1];
                $value = '';
                if (isset($argv[$i + 1]) && $argv[$i + 1][0] != '-') {
                    $value = $argv[$i + 1];
                    $i++;
                }
                $this->set($key, $value);

            } elseif (preg_match('/^--([a-zA-Z]+)$/', $item, $matchCommands)) {
                $cmd = $matchCommands[1];
                $value = '';
                if (isset($argv[$i + 1]) && $argv[$i + 1][0] != '-') {
                    $value = $argv[$i + 1];
                    $i++;
                }
                $this->setCommand($cmd, $value);
            } else {

                if (preg_match('/^\/+(.*)/', $item, $mts)) {
                    $item = $mts[1];
                }

                $data = $this->parsePath($item);

                foreach ($data as $k => $v) {
                    $this->set($k, $v);
                }
            }
        }

        unset($argv);
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/1/16
     * Time: 13:56
     *
     * @return mixed|string
     */
    function getModule(){
        return $this->module;
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/1/16
     * Time: 13:55
     *
     * @return mixed|string
     */
    function getController(){
        return $this->controller;
    }

    /***
     * Desc:
     * User: Peter
     * Date: 2019/1/16
     * Time: 13:56
     *
     * @return mixed|string
     */
    function getAction(){
        return $this->action;
    }
    /**
     * @param $key
     * @param $value
     */
    function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    function get($key = null)
    {
        if ($key == null || func_num_args() == 0) {
            return $this->data;
        }
        return Arr::get($this->data,$key);
    }

    /**
     * @param $cmd
     * @param $value
     */
    function setCommand($cmd, $value)
    {
        $this->commands[$cmd] = $value;
    }

    /**
     * @param null $cmd
     * @return array|mixed
     */
    function getCommand($cmd = null)
    {
        if ($cmd == null) {
            return $this->commands;
        }
        return $this->commands[$cmd];
    }

    /**
     *
     * @param string $path key1/value1/key2/value2/key3/value3
     * @param string $sep
     * @return array [key1=>value1,key2=>value2,...]
     */
    function parsePath($path, $sep = "/")
    {

        $result = array();

        if (!empty($path) && $path[0] == $sep) {
            $path = substr($path, 1);
        }

        $info = explode($sep, $path);
        if (count($info) < 2) {
            return $result;
        }

        for ($i = 0; $i < count($info); $i++) {

            if (isset($info[$i + 1])) {

                $result[$info[$i]] = $info[$i + 1];
                $i++;
            }

        }
        return $result;
    }
}