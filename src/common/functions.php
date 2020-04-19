<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/21
 * Time: 1:16
 */

use Jasmine\App;
use Jasmine\util\Str;
use Jasmine\helper\Config;
use Jasmine\helper\Server;
use Jasmine\library\http\Url;
use Jasmine\library\http\Request;
use Jasmine\library\http\Response;
use Jasmine\library\support\HigherOrderTapProxy;
use Jasmine\library\validate\Validator;
use Jasmine\library\Model;
use Jasmine\library\page\Paginator;
use Jasmine\library\contracts\support\Htmlable;

/*
 |----------------------------------
 | 资源目录
 |----------------------------------
 |
 | 网站
 */
if (!function_exists('assets')) {
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

/**
 * @param $route
 * @return array
 * itwri 2020/3/14 22:49
 */
function mvc(string $route = '')
{

    /**
     * 去掉特殊字符
     */
    $route = trim($route);
    $route = trim($route, '/');

    $route = str_replace('.', '/', $route);

    if(is_null($route) || empty($route)){
        return [App::init()->getRequest()->getModule(),App::init()->getRequest()->getController(),App::init()->getRequest()->getAction(),[]];
    }

    /**
     * 转为数组
     */
    $arr = explode('/', $route);

    $len = count($arr);
    /**
     * 只有一项的情况
     */
    if($len == 1){
        $action = array_shift($arr);
        return [App::init()->getRequest()->getModule(),App::init()->getRequest()->getController(),$action,[]];
    }
    /**
     * 只有2项的情况
     */
    if($len == 2){
        $controller = array_shift($arr);
        $action = array_shift($arr);
        return [App::init()->getRequest()->getModule(),$controller,$action,[]];
    }
    /**
     * 其它情况
     */
    $module = array_shift($arr);
    $controller = array_shift($arr);
    $action = array_shift($arr);

    /**
     * 额外参数
     */
    $extraParamsStr = implode('/', $arr);

    return [$module, $controller, $action,Url::pathToParams($extraParamsStr)];
}

/**
 * URL
 */
if (!function_exists('url')) {
    /**
     * @param $route
     * @param array $params
     * @param bool|string $root
     * @return string
     * itwri 2020/1/6 22:42
     */
    function url($route, $params = [], $root = true)
    {
        list($module,$controller,$action,$extraParams) = mvc($route);

        /**
         * 合并参数
         */
        $params = array_merge($extraParams, $params);

        /**
         * 获取根地址
         */
        $rootUrl = '';
        if ($root === true) {
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

/**
 * 语言
 */
if (!function_exists('lang')) {
    /**
     * @param $key
     * @param array $context
     * @return mixed
     * itwri 2020/3/13 0:02
     */
    function lang($key, array $context = array())
    {
        $value = Config::get(implode('.', ['lang', 'languages', Config::get('lang.default', 'zh-cn'), $key]));
        if (is_null($value)) {
            return $key;
        }
        return format(strval($value), $context);
    }
}

/**
 * 格式化
 */
if (!function_exists('format')) {
    /**
     * @param string $str
     * @param array $context
     * @return string
     * itwri 2020/3/13 0:28
     */
    function format(string $str, array $context)
    {
        //time
        $timeArr = explode(' ', microtime(false));

        if (false !== strpos($str, '{')) {
            $replacements = array();
            foreach ($context as $key => $val) {
                if (null === $val || is_scalar($val) || (\is_object($val) && method_exists($val, '__toString'))) {
                    $replacements["{{$key}}"] = $val;
                } elseif ($val instanceof \DateTimeInterface) {
                    $replacements["{{$key}}"] = $val->format('Y-m-d H:i:s') . substr(strval($timeArr[0]), 1);
                } elseif (\is_object($val)) {
                    $replacements["{{$key}}"] = '[object ' . \get_class($val) . ']';
                } else {
                    $replacements["{{$key}}"] = '[' . \gettype($val) . ']';
                }
            }

            $str = strtr($str, $replacements);
        }

        return $str;
    }
}

/**
 * Tab event
 */
if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed $value
     * @param  callable|null $callback
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
function request()
{
    return App::init()->getRequest();
}

/**
 * @return Response
 * itwri 2020/2/29 23:29
 */
function response()
{
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
function app($class = null, ...$args)
{
    if (!is_null($class)) {
        $arguments = func_get_args();
        $className = array_shift($arguments);
        $class = new \ReflectionClass($className);
        return $class->newInstanceArgs($arguments);
    }
    return App::init();
}

/**
 * 验证器
 */
if (!function_exists('validator')) {
    /**
     * @param $name
     * @return Validator|object
     * itwri 2020/2/29 23:57
     */
    function validator($name)
    {
        if (is_null($name) || empty($table)) {
            return new Validator();
        }
        $class = implode('\\', ['app', request()->getModule(), 'validate', $name]);
        return new $class;
    }
}


//分页
if (!function_exists('paginator')) {
    /**
     * @param $total
     * @param int $page
     * @param string $url
     * @param int $perPageSize
     * @param array $config
     * @return Paginator
     * itwri 2020/3/1 0:13
     */
    function paginator($total, $page = 1, $url = '', $perPageSize = 15, $config = [])
    {
        $args = func_get_args();
        array_unshift($args, Paginator::class);
        return call_user_func_array('\app', $args);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  Htmlable|string $value
     * @param  bool $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (!function_exists('db')) {

    /**
     * @param null $flag
     * @return \Jasmine\library\db\Database|null
     * itwri 2020/3/8 15:12
     */
    function db($flag = null)
    {
        return App::init()->getDb($flag);
    }
}

/**
 * 创建 Model 实例
 */
if (!function_exists('model')) {
    /**
     * @param null $name
     * @param null $layer
     * @return Model
     * itwri 2020/4/12 21:47
     */
    function model($name = null,$layer = null)
    {
        if (is_null($name) || empty($name)) {
            return (new Model())->table($name);
        }
        $class = implode('\\', ['app', is_string($layer)?$layer:Str::studly(request()->getModule()), 'model', ucfirst(Str::camel($name))]);
        return new $class;
    }
}

/**
 * create a Model for table
 */
if (function_exists('table')) {
    /**
     * @param $name
     * @return Model
     * itwri 2020/3/8 15:18
     */
    function table($name)
    {
        return \model($name);
    }
}
