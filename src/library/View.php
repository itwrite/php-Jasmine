<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/21
 * Time: 0:09
 */

namespace Jasmine\library;


use Jasmine\App;
use Jasmine\helper\Config;
use Jasmine\library\view\Template;

class View
{

    /**
     * @var array
     */
    protected static $views = [];

    /**
     * @param $name
     * @param $data
     * @return mixed
     * itwri 2019/12/21 0:37
     */
    public static function make($name, $data = [])
    {

        strpos($name, '@') != false && list($module, $name) = explode('@', $name);

        $viewDirectory = Config::get('view.directory');
        /**
         * view
         */
        !is_dir($viewDirectory) && $viewDirectory = implode(DIRECTORY_SEPARATOR, [
            App::init()->getAppPath(),
            isset($module) ? $module : App::init()->getRequest()->getModule(),
            'view',
        ]);

        /**
         * 初始化
         */
        if (!isset(self::$views[$name]) || !self::$views[$name] instanceof Template) {
            /**
             * cache
             */
            $cacheDirectory = Config::get('view.cache.directory');
            !is_dir($cacheDirectory) && $cacheDirectory = implode(DIRECTORY_SEPARATOR, [
                App::init()->getRuntimePath(),
                'views',
                App::init()->getRequest()->getModule(),
            ]);

            self::$views[$name] = new Template($viewDirectory, $cacheDirectory);
        }

        if(isset($module)){
            self::$views[$name]->setViewDirectory($viewDirectory);
        }

        /**
         * make it
         */
        return self::$views[$name]->make($name, $data)->render();
    }

}