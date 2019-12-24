<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/19
 * Time: 14:59
 */

namespace Jasmine\library;


use Jasmine\App;
use Jasmine\helper\Config;
use Jasmine\library\http\Response;
use Jasmine\library\view\Template;

abstract class Controller
{
    /**
     * @var App|null
     */
    public $app = null;

    /**
     * @var http\Request|null
     */
    private $Request = null;

    /**
     * @var http\Response|null
     */
    private $Response = null;

    /**
     * @var Template|null
     */
    protected $Template = null;
    /**
     * Controller constructor.
     * @param App|null $app
     */
    function __construct(App $app = null)
    {
        $this->app = $app instanceof App ? $app : App::init();
        $this->Request = $this->app->getRequest();
        $this->Response = $this->app->getResponse();
        $this->Template = $this->getTemplate();
    }

    /**
     * @param string $msg
     * @param array $data
     * @param mixed $type
     * @return array|false|string
     */
    function success($msg = '', $data = null, $type = 'json')
    {
        if ($type == 'json' || $this->Request->isJson() || is_array($data)) {
            $this->Response->setContentType('application/json')->getHeader()->send();
            return json_encode(['code' => 200, 'message' => $msg, 'data' => $data]);
        }
        return $data;
    }

    /**
     * @param string $msg
     * @param array $data
     * @param int $code
     * @param mixed $type
     * @return array|false|string
     */
    function error($msg = '', $code = 0, $data = [], $type = 'json')
    {
        if ($type == 'json' || $this->Request->isJson() || is_array($data)) {
            $this->Response->setContentType('application/json')->getHeader()->send();
            return json_encode(['code' => $code, 'message' => $msg, 'data' => $data]);
        }
        return $data;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 17:58
     *
     * @return App|null
     */
    function app()
    {
        return $this->app;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 11:30
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return http\Request|mixed|null
     */
    function request($key = '', $default = null, $filter = null)
    {
        if (func_num_args() == 0 || $key == null) {
            return $this->Request;
        }

        return call_user_func_array([$this->Request, 'input'], func_get_args());
    }

    /**
     * @return http\Request|null
     * itwri 2019/7/31 0:39
     */
    function getRequest(){
        return $this->Request;
    }

    /**
     * @return Response|null
     */
    function getResponse()
    {
        return $this->Response;
    }

    /**
     * itwri 2019/12/21 20:20
     */
    function getTemplate(){
        if(!$this->Template instanceof Template){
            $viewDirectory = Config::get('view.directory');

            /**
             * view
             */
            !is_dir($viewDirectory) && $viewDirectory = implode(DIRECTORY_SEPARATOR, [
                App::init()->getAppPath(),
                isset($module) ? $module : App::init()->getRequest()->getModule(),
                'view',
            ]);

            $this->Template = new Template($viewDirectory,$this->app()->getRuntimePath().DIRECTORY_SEPARATOR.'cache');
        }
        return $this->Template;
    }
    /**
     * @param $key
     * @param $value
     * @return $this
     * itwri 2019/12/20 16:26
     */
    function assign($key,$value = ''){
        call_user_func_array([$this->Template,'assign'],func_get_args());
        return $this;
    }

    /**
     * @param $name
     * @param $data
     * @return string
     * @throws \Exception
     * itwri 2019/12/20 16:20
     */
    function fetch($name,$data = []){
        /**
         * 判断模块
         */
        strpos($name, '@') != false && list($module, $name) = explode('@', $name);

        if(isset($module)){
            /**
             * view
             */
            $viewDirectory = implode(DIRECTORY_SEPARATOR, [
                App::init()->getAppPath(),
                isset($module) ? $module : App::init()->getRequest()->getModule(),
                'view',
            ]);
            $this->getTemplate()->setViewDirectory($viewDirectory);
        }

        return $this->getTemplate()->make($name,$data)->render();
    }
}