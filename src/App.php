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
use Jasmine\helper\Config;
use Jasmine\library\cache\Cache;
use Jasmine\library\console\Console;
use Jasmine\library\db\Database;
use Jasmine\library\file\File;
use Jasmine\library\http\Request;
use Jasmine\library\http\Response;

require_once 'helper/Autoloader.php';
/**
 * ------------------------------------
 * 框架类前缀
 * ------------------------------------
 *
 * 先注册框架的目录
 */
Autoloader::register([__NAMESPACE__ . '\\' => __DIR__]);


class App
{
    const VERSION = '1.0.0';

    /**
     * The current globally available container (if any).
     *
     * @var App
     */
    protected static $instance;

    protected $basePath = '';

    protected $namespace = 'app';
    protected $beginTime = 0;
    protected $beginMem = '';

    protected $Request = null;
    protected $Response = null;
    /** @var Console */
    protected $Console = null;
    protected $Logger = null;

    function __construct($basePath = null)
    {
        $this->setBasePath($basePath);

        /**
         * 记录开始时间
         */
        $this->beginTime = microtime(true);

        /**
         * 记录开始时的内存使用情况
         */
        $this->beginMem = memory_get_usage();

        /**
         * 日志实例
         */
        $this->Logger = Log::getInstance();

        Config::set('PATH_FRAMEWORK',__DIR__);

        foreach ([
            'PATH_APPS'=>$this->appPath(),
            'PATH_ROOT'=>$this->basePath(),
            'PATH_CONFIG'=>$this->configPath(),
            'PATH_RUNTIME'=>$this->runtimePath()] as $key=>$value) {

            Config::set($key,$value);
        }
    }


    /**
     * @param $basePath
     * @param null $callback
     * itwri 2020/3/31 23:21
     */
    public static function start($basePath,$callback = null)
    {
        $app = self::init($basePath);
        /**
         * 回调是留给应用层提前初始化
         * 如果返回的是false，强制退出
         */
        if (is_callable($callback) && call_user_func_array($callback, array($app)) === false) exit("exit anyway!!");

        /*
        |--------------------------------------------------------------------------
        | 扩展 AUTOLOAD 加载的目录
        |--------------------------------------------------------------------------
        */
        Autoloader::extend([$app->namespace.'\\' => $app->appPath()]); //应用类前缀

        /**
         * 如果有定义常量PATH_CONFIG,则设到配置项中
         */
        defined('PATH_CONFIG') && Config::set('PATH_CONFIG', PATH_CONFIG);

        /**
         * 如果存在PATH_CONFIG，则加载目录下的文件
         */
        Config::load($app->configPath());

        /**
         * 此部分可通过提前声明全局常量来控制
         */
        //调试模式,默认true
        !is_null(Config::get('app.debug')) or Config::set('app.debug', defined('DEBUG') ? DEBUG : false);


        /**
         * 加载公共文件
         */
        File::import(Config::get('PATH_FRAMEWORK').'/common');
        /**
         * 加载公共文件
         */
        File::import(implode(DIRECTORY_SEPARATOR, [Config::get('PATH_APPS', ''), 'common.php']));

        /**
         *
         */
        if($app->isRunningInConsole()){
            $app->consoleKernel();
            die();
        }

        $app->httpKernel();
    }



    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function isRunningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * @param $basePath
     * itwri 2020/3/31 15:09
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
    }

    /**
     * Get the base path of the App installation.
     *
     * @param  string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the app path of the App installation.
     * @return string
     * itwri 2020/3/31 17:04
     */
    public function appPath()
    {
        return Config::get('PATH_APPS', $this->basePath . DIRECTORY_SEPARATOR . 'app');
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param  string $path Optionally, a path to append to the app path
     * @return string
     */
    public function path($path = '')
    {
        return $this->appPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return Config::get('PATH_PUBLIC',$this->basePath . DIRECTORY_SEPARATOR . 'public');
    }

    /**
     * Get the path to the config / web directory.
     *
     * @return string
     */
    public function configPath()
    {
        return Config::get('PATH_CONFIG',$this->basePath . DIRECTORY_SEPARATOR . 'config');
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function runtimePath()
    {
        return Config::get('PATH_RUNTIME', $this->basePath . DIRECTORY_SEPARATOR . 'runtime');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->Request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->Response;
    }

    /**
     * @var Cache|null
     */
    protected static $Cache = null;

    /**
     * @return Cache|null
     * itwri 2020/2/12 17:05
     */
    function getCache()
    {
        if (self::$Cache == null) {
            self::$Cache = new Cache(Config::get('cache', []));
        }
        return self::$Cache;
    }

    /**
     * @return Log|null
     * itwri 2020/3/8 14:47
     */
    function &getLogger()
    {
        return $this->Logger;
    }

    /**
     * itwri 2020/3/31 23:38
     */
    protected function httpKernel()
    {
        /**
         * 初始化请求类和响应类
         */
        $this->Request = new Request(Config::get('request', []));
        $this->Response = new Response();

        $this->getLogger()->info(str_pad('-', 50, '-'));
        $this->logPerformanceInfo('[开始]');
        $this->getLogger()->info(sprintf('[%s] %s, %s, %s', $this->getRequest()->getScheme(), $this->getRequest()->ip(), $this->getRequest()->getMethod(), $this->getRequest()->getUri()));
        $this->getLogger()->info('request-header:');
        $this->getLogger()->info([
            'uri' => $this->getRequest()->getUri(),
            'host' => $this->getRequest()->getHost(),
            'method' => $this->getRequest()->getMethod(),
            'header' => $this->getRequest()->header(),
            'params' => $this->getRequest()->param()
        ]);

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

            /**
             * 实例化
             */
            $controller_instance = app($controller_class, $this);

            /**
             * 检查操作的合法性，并调起对应的操作方法
             */
            if (!empty($action) && is_callable(array($controller_instance, $action))) {

                //调用对应的操作方法方
                $this->getResponse()->setData(call_user_func_array(array($controller_instance, $action), array($this->getRequest())));
                $this->getResponse()->send();
                $this->logPerformanceInfo('[结束]');
                die();
            } elseif (!empty($action)) {
                throw new \ErrorException("非法操作");
            }

        } catch (\Exception $exception) {
            /**
             * write in log.
             */
            $this->getLogger()->error((string)$exception);

            if (Config::get('app.debug')) {
                print_r("Error: " . (string)$exception . PHP_EOL);
            } else {
                print_r("Error: " . $exception->getMessage() . PHP_EOL);
            }
        }
    }

    /**
     * itwri 2020/3/31 23:10
     */
    protected function consoleKernel()
    {
        /**
         * 控制台
         */
        $this->Console = new Console();

        /**
         * 打印日志
         */
        $this->getLogger()->info(str_pad('-', 50, '-'));
        $this->logPerformanceInfo('[开始]');
        $this->getLogger()->info(sprintf('[cmd]: %s', $this->Console->getInput()->getScript()));

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
            $controller_class = $this->parseAppClass($module, 'commands', $controller);

            /**
             * 导入应用层公共文件
             */
            $this->importAppCommonFile($module);

            //Log
            $this->logPerformanceInfo();

            /**
             * 实例化
             */
            $controller_instance = app($controller_class, $this);

            /**
             * 检查操作的合法性，并调起对应的操作方法
             */
            if (empty($action) || !is_callable(array($controller_instance, $action))) {
                throw new \ErrorException("非法操作");
            }

            //调用对应的操作方法方
            print_r(call_user_func_array(array($controller_instance, $action), array($this)));
            $this->logPerformanceInfo('[结束]');
            die();

        } catch (\Exception $exception) {
            /**
             * write in log.
             */
            $this->getLogger()->error((string)$exception);

            if (Config::get('app.debug')) {
                print_r("Error: " . (string)$exception . PHP_EOL);
            } else {
                print_r("Error: " . $exception->getMessage() . PHP_EOL);
            }
        }
    }

    /**
     * @return mixed|string
     * itwri 2020/5/15 16:33
     */
    public function getModule(){
        if($this->isRunningInConsole()){
            return $this->Console->getInput()->getModule();
        }
        return $this->getRequest()->getModule();
    }

    /**
     * @return mixed|string
     * itwri 2020/5/15 16:33
     */
    public function getController(){
        if($this->isRunningInConsole()){
            return $this->Console->getInput()->getController();
        }
        return $this->getRequest()->getController();
    }

    /**
     * @return mixed|string
     * itwri 2020/5/15 16:33
     */
    public function getAction(){
        if($this->isRunningInConsole()){
            return $this->Console->getInput()->getAction();
        }
        return $this->getRequest()->getAction();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     * itwri 2019/12/19 17:54
     */
    public function config($key = '', $default = null)
    {
        return call_user_func_array(implode('::', [Config::class, 'get']), func_get_args());
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
        $connections_config = Config::get('db.connections', []);

        /**
         * 如果传入的type 不为null
         * 返回对应数据的Database实例
         */
        if ($flag != null && is_string($flag)) {

            if ((!isset(self::$dbs[$flag])) || (!self::$dbs[$flag] instanceof Database)) {

                $config = isset($connections_config[$flag]) ? $connections_config[$flag] : [];

                self::$dbs[$flag] = new Database($config, $this->getLogger());

                if (isset($config['debug']) && $config['debug']) {
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
            $db_key = $flag ? $flag : Config::get('db.default', 'mysql');
            /**
             * 取出db config
             */
            $config = isset($connections_config[$db_key]) ? $connections_config[$db_key] : [];

            /**
             * 初始化Database
             */
            self::$db = new Database($config, $this->getLogger());
            /**
             * 如果配置中设置debug模式，则将db设为debug = true
             */
            if (isset($config['debug']) && $config['debug']) {
                self::$db->debug(1);
            }
            return self::$db;
        }

        return self::$db;
    }


    /**
     * 初始化App实例
     * @param $basePath
     * @return App
     * itwri 2020/3/31 23:21
     */
    static public function init($basePath = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($basePath);
        }
        return self::$instance;
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

        $class = $this->parseName(array_pop($array), 1) . ($appendSuffix ? ucfirst($layer) : '');


        $path = $array ? implode('\\', $array) . '\\' : '';

        return $this->namespace . '\\' . ($module ? $module . '\\' : '') . $layer . '\\' . $path . $class;
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

    /**
     * @param string $prefix
     * @return $this
     * itwri 2020/3/4 18:44
     */
    protected function logPerformanceInfo($prefix = '')
    {
        //[运行时间：2.189942s] [吞吐率：0.46req/s] [内存消耗：3,496.66kb] [文件加载：139]
        $runtime = round(microtime(true) - $this->beginTime, 10);
        $reqs = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $memory_use = number_format((memory_get_usage() - $this->beginMem) / 1024, 2);

        $time_str   = '[运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_str = '[内存消耗：' . $memory_use . 'kb]';
        $file_load  = '[文件加载：' . count(get_included_files()) . ']';

        $this->getLogger()->info($prefix . $time_str . $memory_str . $file_load);
        return $this;
    }

    /**
     * @param $module
     * itwri 2020/3/8 14:47
     */
    protected function importAppCommonFile($module)
    {
        /**
         * 加载模块公共文件
         */
        File::import(implode(DS, [Config::get('PATH_APPS', ''), $module, 'common.php']));

        /**
         * 加载模块下的配置
         */
        Config::load(implode(DS, [Config::get('PATH_APPS', ''), $module, 'config']));
    }
}