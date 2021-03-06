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
use Jasmine\library\validate\Validator;
use Jasmine\library\view\Template;
use Jasmine\util\Str;

abstract class Controller
{
    /**
     * @var App|null
     */
    private $app = null;

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
    private $Template = null;

    /**
     * @var mixed | Validator
     */
    protected $Validator = null;

    /**
     * @var mixed | Model
     */
    protected $Model = null;

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
        $this->Model = new Model();
        $this->Validator = new Validator();
    }

    /**
     * @param string $msg
     * @param array $data
     * @param mixed $type
     * @return array|false|string
     */
    protected function success($msg = '', $data = null, $type = 'json')
    {
        if ($type == 'json' || $this->Request->isJson() || is_array($data)) {
            $this->Response->setContentType('application/json')->getHeader()->send();
            return json_encode(['code' => 200, 'message' => lang($msg), 'data' => $data]);
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
    protected function error($msg = '', $code = 0, $data = null, $type = 'json')
    {
        /**
         * 如果参数小于2，即只有一个参数，或者传入的code 是null
         * 则参考msg是否为数字
         */
        if(func_num_args()<2 || is_null($code)){
            $code = is_numeric($msg) ? $msg : $code;
        }

        /**
         * 符合以下条件的，返回json字符串内容
         */
        if ($type == 'json' || $this->Request->isJson() || is_array($data)) {
            $this->Response->setContentType('application/json')->getHeader()->send();
            return json_encode(['code' => intval($code), 'message' => lang($msg), 'data' => $data]);
        }
        return $data;
    }

    /**
     * @param null $class
     * @param mixed ...$args
     * @return App|object|null
     * itwri 2020/2/29 22:02
     */
    protected function app($class = null,...$args)
    {
        if(!is_null($class)){
            return call_user_func_array('\app',func_get_args());
        }
        return $this->app;
    }

    /**
     * @return Model|mixed
     * itwri 2020/2/29 23:41
     */
    protected function getModel(){
        return $this->Model;
    }

    /**
     * @param Model $model
     * @return $this
     * itwri 2020/2/29 23:41
     */
    protected function setModel(Model $model){
        $this->Model = $model;
        return $this;
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
    protected function request($key = '', $default = null, $filter = null)
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
    protected function getRequest()
    {
        return $this->Request;
    }

    /**
     * @return Response|null
     */
    protected function getResponse()
    {
        return $this->Response;
    }

    /**
     * @return Validator|mixed
     * itwri 2020/2/29 21:44
     */
    protected function getValidator(){
        if(is_null($this->Validator)){
            $this->Validator = new Validator();
        }
        return $this->Validator;
    }

    /**
     * @param Validator $validator
     * @return $this
     * itwri 2020/2/29 21:45
     */
    protected function setValidator(Validator $validator){
        $this->Validator = $validator;
        return $this;
    }

    /**
     * itwri 2019/12/21 20:20
     */
    protected function getTemplate()
    {
        if (!$this->Template instanceof Template) {
            $viewDirectory = Config::get('view.directory');

            /**
             * view directory
             */
            !is_dir($viewDirectory) && $viewDirectory = implode(DIRECTORY_SEPARATOR, [
                App::init()->appPath(),
                isset($module) ? $module : App::init()->getRequest()->getModule(),
                'view',
            ]);

            /**
             * cache directory
             */
            $cacheDirectory = implode(DIRECTORY_SEPARATOR, [
                App::init()->runtimePath(),
                'views',
                App::init()->getRequest()->getModule(),
            ]);

            $this->Template = new Template($viewDirectory, $cacheDirectory);
        }
        return $this->Template;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * itwri 2019/12/20 16:26
     */
    protected function assign($key, $value = '')
    {
        call_user_func_array([$this->Template, 'assign'], func_get_args());
        return $this;
    }

    /**
     * @param $name
     * @param $data
     * @return string
     * itwri 2019/12/20 16:20
     */
    protected function fetch($name = '', $data = [])
    {

        try {

            $name = empty($name) ? implode('.', [Str::snake($this->getRequest()->getModule()),Str::snake($this->getRequest()->getController()),Str::snake($this->getRequest()->getAction())]) : $name;
            /**
             * 判断模块
             */
            strpos($name, '@') != false && list($module, $name) = explode('@', $name);

            if (isset($module)) {
                /**
                 * view
                 */
                $viewDirectory = implode(DIRECTORY_SEPARATOR, [
                    App::init()->appPath(),
                    isset($module) ? $module : App::init()->getRequest()->getModule(),
                    'view',
                ]);
                $this->getTemplate()->setViewDirectory($viewDirectory);
            }

            return $this->getTemplate()->make($name, $data)->render();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }


}