<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/26
 * Time: 16:40
 */

require_once __DIR__.'/../../../../../framework/src/App.php';

use \Jasmine\App;
use \Jasmine\helper\Config;
App::init()->web(function(App $app){
    /**
     * 项目目录
     */
    Config::set('PATH_APPS',__DIR__."/../app");

    /**
     * 全局配置目录
     */
    Config::set('PATH_CONFIG',__DIR__."/../config");

    /**
     * 缓存以及编译文件目录
     */
    Config::set('PATH_RUNTIME',__DIR__."/../runtime");
});