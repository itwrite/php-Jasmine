<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/21
 * Time: 1:16
 */

/**
 * @param $path
 * @return string
 * itwri 2019/12/21 1:16
 */

function assets($path){
    //explode the script path
    $arr = explode('/', \Jasmine\helper\Server::get('PHP_SELF', ''));
    //the last one is the filename,so remove it;
    array_pop($arr);

    return implode('/',$arr).'/assets/'.$path;
}

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
            $module = \Jasmine\App::init()->getRequest()->getModule();
            $controller = \Jasmine\App::init()->getRequest()->getController();
            $action = array_shift($arr);
            break;
        case 2:
            $module = \Jasmine\App::init()->getRequest()->getModule();
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
    $module = $module ? $module : \Jasmine\App::init()->getRequest()->getModule();
    $controller = $controller ? $controller : \Jasmine\App::init()->getRequest()->getController();
    $action = $action ? $action : \Jasmine\App::init()->getRequest()->getAction();

    /**
     * 额外参数
     */
    $extraParams = implode('/', $arr);

    /**
     * 合并参数
     */
    $params = array_merge(\Jasmine\library\http\Url::pathToParams($extraParams), $params);

    /**
     * 获取根地址
     */
    $rootUrl = '';
    if ($root == true) {
        $rootUrl = \Jasmine\App::init()->getRequest()->getRootUrl().\Jasmine\App::init()->getRequest()->getScriptName();
    } elseif (is_string($root)) {
        $rootUrl = $root;
    }

    /**
     * 合并参数
     */
    $params = array_merge([
        \Jasmine\helper\Config::get('request.var_module','m') => $module,
        \Jasmine\helper\Config::get('request.var_controller','c') => $controller,
        \Jasmine\helper\Config::get('request.var_action','a') => $action,
    ],$params);

    /**
     * 转为Url
     * 返回链接地址
     */
    $Url = new \Jasmine\library\http\Url($rootUrl);

    return $Url->setParam($params)->toString();
}

/**
 * @param $key
 * @return mixed
 * itwri 2020/1/22 15:03
 */
function lang($key){
    return \Jasmine\helper\Config::get(implode('.',['lang','languages',\Jasmine\helper\Config::get('lang.default','zh-cn'),$key]),$key);
}