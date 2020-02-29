<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/7/17
 * Time: 14:16
 */

namespace Jasmine;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

use Jasmine\helper\Autoloader;
use Jasmine\helper\Log;
use Jasmine\helper\Server;
use Jasmine\helper\Config;
use Jasmine\library\cache\Cache;
use Jasmine\library\console\Console;
use Jasmine\library\db\Database;
use Jasmine\library\file\File;
use Jasmine\library\http\Request;
use Jasmine\library\http\Response;

require_once 'library/file/File.php';
require_once 'helper/Config.php';
require_once 'common/functions.php';
require_once 'helper/Log.php';
require_once 'helper/Autoloader.php';


class App
{
    protected $config = null;
    protected $rootPath = '';
    protected $debug = false;
    protected $appPath = '';
    protected $appNamespace = 'app';
    protected $runtimePath = '';
    protected $beginTime = 0;
    protected $beginMem = '';
    protected $Request = null;
    protected $Response = null;
    protected $Console = null;

    function __construct()
    {
        /**
         * 记录开始时间
         */
        $this->beginTime = microtime(true);

        /**
         * 记录开始时的内存使用情况
         */
        $this->beginMem = memory_get_usage();

        /*
       |--------------------------------------------------------------------------
       | 初始化一些常量和配置
       |--------------------------------------------------------------------------
       */
        /**
         * 取入口文件的目录为根目录
         * 保存到全局变量中
         */
        $this->rootPath = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
        Config::set('PATH_ROOT', $this->rootPath);

        /**
         * 此部分可通过提前声明全局常量来控制
         */
        //调试模式,默认true
        !is_null(Config::get('app.debug')) or Config::set('app.debug', defined('DEBUG') ? DEBUG : true);
        $this->debug = Config::get('app.debug',false);


        !is_null(Config::get('PATH_COMMON')) or Config::set('PATH_COMMON', defined('PATH_COMMON') ? PATH_COMMON : dirname($this->rootPath) . DS . 'common');

        /**
         * 应用目录，可通过提前声明的常量进行设置，也可以通过config去设置
         * 默认为入口文件目录下的Application目录
         */
        !is_null(Config::get('PATH_APPS')) or Config::set('PATH_APPS', defined('PATH_APPS') ? PATH_APPS : dirname($this->rootPath) . DS . 'app');
        $this->appPath = Config::get('PATH_APPS');

        /**
         * 缓存以及编译文件的根目录
         */
        !is_null(Config::get('PATH_RUNTIME')) or Config::set('PATH_RUNTIME', defined('PATH_RUNTIME') ? PATH_RUNTIME : dirname($this->rootPath) . DS . 'runtime');
        $this->runtimePath = Config::get('PATH_RUNTIME');

        /*
        |--------------------------------------------------------------------------
        | 注册AUTOLOAD
        |--------------------------------------------------------------------------
        */
        Autoloader::register([
            __NAMESPACE__.'\\'=>__DIR__, //框架类前缀
            $this->appNamespace . '\\' =>$this->appPath, //应用类前缀
        ]);

        /**
         * 加载公共文件
         */
        File::import(Config::get('PATH_COMMON', ''));
        /**
         * 加载公共文件
         */
        File::import(implode(DS, [Config::get('PATH_APPS', ''), 'common.php']));
    }

    /**
     * @return Request
     */
    function getRequest(){
        return $this->Request;
    }

    /**
     * @return Response
     */
    function getResponse(){
        return $this->Response;
    }

    /**
     * @return string
     * itwri 2019/12/20 16:18
     */
    function getRootPath(){
        return $this->rootPath;
    }

    /**
     * @return mixed|string
     * itwri 2019/12/20 16:18
     */
    function getAppPath(){
        return $this->appPath;
    }

    /**
     * @return mixed|string
     * itwri 2019/12/20 16:18
     */
    function getRuntimePath(){
        return $this->runtimePath;
    }

    /**
     * @var Cache|null
     */
    static protected $Cache = null;

    /**
     * @return Cache|null
     * itwri 2020/2/12 17:05
     */
    function getCache(){
        if(self::$Cache == null){
            self::$Cache = new Cache(Config::get('cache.type',''),['root_path'=>Config::get('cache.path')]);
        }
        return self::$Cache;
    }

    /**
     * @return $this
     * itwri 2020/2/27 12:05
     */
    function logPerformanceInfo(){
        //[运行时间：2.189942s] [吞吐率：0.46req/s] [内存消耗：3,496.66kb] [文件加载：139]
        $runtime = round(microtime(true) - $this->beginTime, 10);
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $memory_use = number_format((memory_get_usage() - $this->beginMem) / 1024, 2);

        $time_str   = '[运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_str = '[内存消耗：' . $memory_use . 'kb]';
        $file_load  = '[文件加载：' . count(get_included_files()) . ']';

        Log::info($time_str.$memory_str.$file_load);
        return $this;
    }

    function importAppCommonFile($module){

        /**
         * 加载模块公共文件
         */
        File::import(implode(DS, [Config::get('PATH_APPS', ''), $module, 'common.php']));

        /**
         * 加载模块下的配置
         */
        Config::load(implode(DS, [Config::get('PATH_APPS', ''), $module, 'config']));

        return $this;
    }

    /**
     * @param null $callback
     * itwri 2020/2/29 22:17
     */
    function web($callback = null){


        /**
         * 回调是留给应用层提前初始化
         * 如果返回的是false，强制退出
         */
        if (is_callable($callback) && call_user_func_array($callback, array($this)) === false) exit("exit anyway!!");

        /**
         * 如果有定义常量PATH_CONFIG,则设到配置项中
         */
        defined('PATH_CONFIG') && Config::set('PATH_CONFIG', PATH_CONFIG);

        /**
         * 如果存在PATH_CONFIG，则加载目录下的文件
         */
        !is_null(Config::get('PATH_CONFIG')) && Config::load(Config::get('PATH_CONFIG'));

        /**
         * 初始化请求类和响应类
         */
        $this->Request = new Request(Config::get('request',[]));
        $this->Response = new Response();

        Log::info(sprintf('-- start with [%s]: %s %s %s',$this->getRequest()->getScheme(),$this->getRequest()->ip(),$this->getRequest()->getMethod(),$this->getRequest()->getUri()));

        try {
            /**
             * 访问的模块
             */
            $module = $this->Request->getModule();

            if (empty($module)) {
                throw new \ErrorException("Module can not be empty");
            }

            $controller = ucfirst($this->Request->getController());


            if (empty($controller)) {
                throw new \ErrorException("Controller can not be empty");
            }

            $action = $this->Request->getAction();

            if (empty($action)) {
                throw new \ErrorException("Action can not be empty");
            }
            /**
             * 路由规则
             */
            $controller_class = $this->parseAppClass($module, 'controller', $controller);

            /**
             * 导入应用层公共文件
             */
            $this->importAppCommonFile($module);

            //Log
            $this->logPerformanceInfo();

            /**
             * 实例化
             */
            $controller_instance = app($controller_class,$this);

            /**
             * 检查操作的合法性，并调起对应的操作方法
             */
            if (!empty($action) && is_callable(array($controller_instance, $action))) {

                //调用对应的操作方法方
                $this->getResponse()->setData(call_user_func_array(array($controller_instance, $action), array($this->getRequest())));
                $this->getResponse()->send();
                /**
                 * 打印日志
                 */
                Log::info(json_encode([
                    'uri'=>$this->getRequest()->getUri(),
                    'host'=>$this->getRequest()->getHost(),
                    'method'=>$this->getRequest()->getMethod(),
                    'header'=>$this->getRequest()->header(),
                    'params'=>$this->getRequest()->param()
                ],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                die();
            } elseif (!empty($action)) {
                throw new \ErrorException("非法操作");
            }

        } catch (\Exception $exception) {
            /**
             * write in log.
             */
            Log::error((string)$exception);

            $this->debug && print_r("Error: " . (string)$exception . PHP_EOL);
        }
    }

    /**
     * @param null $callback
     * itwri 2020/2/29 22:18
     */
    function console($callback = null){
        /**
         * 控制台
         */
        $this->Console = new Console();

        //如果返回的是false，强制退出
        if (is_callable($callback) && call_user_func_array($callback, array($this)) === false) exit("exit anyway!!");

        defined('PATH_CONFIG') && Config::set('PATH_CONFIG', PATH_CONFIG);

        !is_null(Config::get('PATH_CONFIG')) && Config::load(Config::get('PATH_CONFIG'));

        /**
         * 打印日志
         */
        Log::info(sprintf('-- start with [cmd]: %s',$this->Console->getInput()->getScript()));

        try {
            /**
             * 访问的模块
             */
            $module = $this->Console->getInput()->getModule();

            if (empty($module)) {
                throw new \ErrorException("Module can not be empty");
            }

            $controller = ucfirst($this->Console->getInput()->getController());

            if (empty($controller)) {
                throw new \ErrorException("Controller can not be empty");
            }

            $action = $this->Console->getInput()->getAction();

            if (empty($action)) {
                throw new \ErrorException("Action can not be empty");
            }
            /**
             * 路由规则
             */
            $controller_class = $this->parseAppClass($module, 'command', $controller);

            /**
             * 导入应用层公共文件
             */
            $this->importAppCommonFile($module);

            //Log
            $this->logPerformanceInfo();

            /**
             * 实例化
             */
            $controller_instance = app($controller_class,$this);

            /**
             * 检查操作的合法性，并调起对应的操作方法
             */
            if (!empty($action) && is_callable(array($controller_instance, $action))) {

                //调用对应的操作方法方
                echo call_user_func_array(array($controller_instance, $action), array($this));
                die();
            } elseif (!empty($action)) {
                throw new \ErrorException("非法操作");
            }

        } catch (\Exception $exception) {
            /**
             * write in log.
             */
            Log::error((string)$exception);

            $this->debug && print_r("Error: " . (string)$exception . PHP_EOL);
        }
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     * itwri 2019/12/19 17:54
     */
    public function config($key,$default = null){
        return Config::get($key,$default);
    }

    /**
     * @var Database|null
     */
    static protected $db = null;

    /**
     * @var array
     */
    static protected $dbs = [];

    /**
     * @param null $flag
     * @return Database|null
     * itwri 2019/12/19 0:17
     */
    public function getDb($flag = null)
    {
        /**
         * 获取配置信息
         */
        $connections_config = Config::get('db.connections',[]);

        /**
         * 如果传入的type 不为null
         * 返回对应数据的Database实例
         */
        if($flag != null && is_string($flag)){

            if((!isset(self::$dbs[$flag]))||(!self::$dbs[$flag] instanceof Database)){

                $config = isset($connections_config[$flag])?$connections_config[$flag]:[];

                self::$dbs[$flag] = new Database($config);

                if(isset($config['debug']) && $config['debug']){
                    self::$dbs[$flag]->debug(1);
                }
            }

            return self::$dbs[$flag];
        }

        /**
         * 默认返回静态实例数据库
         */
        if (self::$db == null) {

            /**
             * 如果不传入type，则默认用配置的
             */
            $db_key = $flag ? $flag : Config::get('db.default','mysql');
            /**
             * 取出db config
             */
            $config = isset($connections_config[$db_key])?$connections_config[$db_key]:[];

            /**
             * 初始化Database
             */
            self::$db = new Database($config);
            /**
             * 如果配置中设置debug模式，则将db设为debug = true
             */
            if(isset($config['debug']) && $config['debug']){
                self::$db->debug(1);
            }
            return self::$db;
        }

        return self::$db;
    }

    /**
     * @var null|App
     */
    static protected $_instance = null;

    /**
     * 初始化App实例
     * @return App|null
     * itwri 2020/2/29 15:12
     */
    static public function init(){
        if(self::$_instance == null){
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param  string $module 模块名
     * @param  string $layer 层名 controller model ...
     * @param  string $name 类名
     * @param  bool $appendSuffix
     * @return string
     */
    protected function parseAppClass($module, $layer, $name, $appendSuffix = false)
    {
        /**
         * 替换
         */
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);

        $class = $this->parseName(array_pop($array),1) . ($appendSuffix ? ucfirst($layer) : '');


        $path = $array ? implode('\\', $array) . '\\' : '';

        return $this->appNamespace . '\\' . ($module ? $module . '\\' : '') . $layer . '\\' . $path . $class;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @access public
     * @param  string $name 字符串
     * @param  integer $type 转换类型
     * @param  bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    protected function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}