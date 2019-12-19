<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/19
 * Time: 14:59
 */

namespace Jasmine\library;


use Jasmine\App;
use Jasmine\library\http\Response;

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
     * Controller constructor.
     * @param App|null $app
     */
    function __construct(App $app = null)
    {
        $this->app = $app instanceof App ? $app : App::init();
        $this->Request = $this->app->getRequest();
        $this->Response = $this->app->getResponse();
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
}