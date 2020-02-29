<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/21
 * Time: 1:16
 */

use Jasmine\App;
use Jasmine\helper\Config;
use Jasmine\helper\Server;
use Jasmine\library\http\Url;
use Jasmine\library\http\Request;
use Jasmine\library\http\Response;
use Jasmine\library\support\HigherOrderTapProxy;
use Jasmine\library\validate\Validator;
use Jasmine\library\Model;
use Jasmine\library\page\Paginator;

if (! function_exists('assets')) {
    /**
     * @param $path
     * @return string
     * itwri 2019/12/21 1:16
     */
    function assets($path)
    {
        //explode the script path
        $arr = explode('/', Server::get('PHP_SELF', ''));
        //the last one is the filename,so remove it;
        array_pop($arr);

        return implode('/', $arr) . '/assets/' . $path;
    }
}

if (! function_exists('url')) {
    /**
     * @param $route
     * @param array $params
     * @param bool $root
     * @return string
     * itwri 2020/1/6 22:42
     */
    function url($route, $params = [], $root = true)
    {
        /**
         * 去掉特殊字符
         */
        $route = trim($route, '/');

        /**
         * 转为数组
         */
        $arr = explode('/', $route);

        /**
         * 分析结构
         */
        switch (count($arr)) {
            case 1:
                $module = App::init()->getRequest()->getModule();
                $controller = App::init()->getRequest()->getController();
                $action = array_shift($arr);
                break;
            case 2:
                $module = App::init()->getRequest()->getModule();
                $controller = array_shift($arr);
                $action = array_shift($arr);
                break;
            default:
                $module = array_shift($arr);
                $controller = array_shift($arr);
                $action = array_shift($arr);

        }

        /**
         * 补全数据
         */
        $module = $module ? $module : App::init()->getRequest()->getModule();
        $controller = $controller ? $controller : App::init()->getRequest()->getController();
        $action = $action ? $action : App::init()->getRequest()->getAction();

        /**
         * 额外参数
         */
        $extraParams = implode('/', $arr);

        /**
         * 合并参数
         */
        $params = array_merge(Url::pathToParams($extraParams), $params);

        /**
         * 获取根地址
         */
        $rootUrl = '';
        if ($root == true) {
            $rootUrl = App::init()->getRequest()->getRootUrl() . App::init()->getRequest()->getScriptName();
        } elseif (is_string($root)) {
            $rootUrl = $root;
        }

        /**
         * 合并参数
         */
        $params = array_merge([
            Config::get('request.var_module', 'm') => $module,
            Config::get('request.var_controller', 'c') => $controller,
            Config::get('request.var_action', 'a') => $action,
        ], $params);

        /**
         * 转为Url
         * 返回链接地址
         */
        $Url = new Url($rootUrl);

        return $Url->setParam($params)->toString();
    }
}

if (! function_exists('lang')) {
    /**
     * @param $key
     * @return mixed
     * itwri 2020/1/22 15:03
     */
    function lang($key)
    {
        return Config::get(implode('.', ['lang', 'languages', Config::get('lang.default', 'zh-cn'), $key]), $key);
    }
}

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

/**
 * @return Request
 * itwri 2020/2/29 23:21
 */
function request(){
    return App::init()->getRequest();
}

/**
 * @return Response
 * itwri 2020/2/29 23:29
 */
function response(){
    return App::init()->getResponse();
}

/**
 * 创建实例 或 返回App实例
 * @param null $class
 * @param mixed ...$args
 * @return App|null|object
 * @throws ReflectionException
 * itwri 2020/2/29 22:01
 */
function app($class = null,...$args){
    if(!is_null($class)){
        $arguments = func_get_args();
        $className = array_shift($arguments);
        $class = new \ReflectionClass($className);
        return $class->newInstanceArgs($arguments);
    }
    return App::init();
}

/**
 * 创建 Model 实例
 * @param $name
 * @return Model|object
 * itwri 2020/2/29 23:17
 */
function model($name = null){
    if(is_null($name) || empty($name)){
        return (new Model())->table($name);
    }
    $class = implode('\\',['app',request()->getModule(),'model',ucfirst($name)]);
    return new $class;
}

if(!function_exists('validator')){
    /**
     * @param $name
     * @return Validator|object
     * itwri 2020/2/29 23:57
     */
    function validator($name){
        if(is_null($name) || empty($table)){
            return new Validator();
        }
        $class = implode('\\',['app',request()->getModule(),'validate',ucfirst($name)]);
        return new $class;
    }
}


//分页
if(!function_exists('paginator')){
    /**
     * @param $total
     * @param int $page
     * @param string $url
     * @param int $perPageSize
     * @param array $config
     * @return Paginator
     * itwri 2020/3/1 0:13
     */
    function paginator($total, $page = 1, $url = '', $perPageSize = 15, $config = []){
        $args = func_get_args();
        array_unshift($args,Paginator::class);
        return call_user_func_array('\app',$args);
    }
}
