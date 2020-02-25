<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/7/18
 * Time: 0:47
 */

namespace Jasmine\library\http;


use Jasmine\library\http\request\Header;
use Jasmine\library\http\request\method\Get;
use Jasmine\library\http\request\method\Input;
use Jasmine\library\http\request\method\Post;
use Jasmine\util\Arr;

class Request
{
    protected $Get = null;
    protected $Post = null;
    protected $Input = null;
    protected $Header = null;
    protected $Curl = null;

    protected $Url = null;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'var_action' => 'a',
        'var_module' => 'm',
        'var_controller' => 'c',
        // PATHINFO变量名 用于兼容模式
        'var_route' => 's',
        // 表单请求类型伪装变量
        'var_method' => '_method',
        // 表单ajax伪装变量
        'var_ajax' => '_ajax',
        // 表单pjax伪装变量
        'var_pjax' => '_pjax',

        //默认模块
        'default_module' => 'index',
        //默认控制器
        'default_controller' => 'index',
        //默认操作
        'default_action' => 'index',
        // IP代理获取标识
        'http_agent_ip'    => 'HTTP_X_REAL_IP',

    ];

    /**
     * 请求类型
     * @var string
     */
    private $_method;

    /**
     * @var mixed|string
     */
    protected $module = '';

    /**
     * @var mixed|string
     */
    protected $controller = '';

    /**
     * @var mixed|string
     */
    protected $action = '';

    /**
     * @var array
     */
    protected $extraData = [];

    protected $Cookie = null;


    function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        $this->Url = new Url($this->getUrl());
        $this->Get = new Get($this->getUrl());
        $this->Post = new Post();
        $this->Input = new Input();
        $this->Header = new Header();
        $this->Cookie = new Cookie();
        $this->Curl = new Curl();

        $this->_method = $this->getMethod();

        if ($route = $this->getRawParam($this->config('var_route'))) {

            if ($route && preg_match('/^\/+(.*)/', $route, $mts)) {
                $route = $mts[1];
            }
            $arr = explode('/', $route);
            $this->module = array_shift($arr);
            $this->controller = count($arr) > 0 ? array_shift($arr) : '';
            $this->action = count($arr) > 0 ? array_shift($arr) : '';
            if (count($arr) > 0) {
                $this->extraData = Url::pathToParams(implode('/', $arr));
            }
        }
    }

    /**
     * @param array $keys
     * @return array|bool|mixed|string
     * itwri 2020/2/25 22:20
     */
    function only(array $keys = []){
        if(func_num_args() == 0){
            return $this->param();
        }
        return Arr::only($this->param(),$keys);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 2:06
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function get($key = '', $default = null, $filter = null)
    {
        return call_user_func_array([$this->Get, 'get'], func_get_args());
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 2:06
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function post($key = '', $default = null, $filter = null)
    {
        return call_user_func_array([$this->Post, 'get'], func_get_args());
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:28
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function cookie($key = '', $default = null, $filter = null)
    {
        return call_user_func_array([$this->Cookie,'get'],func_get_args());
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:35
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function file($key = '', $default = null, $filter = null)
    {
        return Arr::get($_FILES, $key, $default, $filter);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:33
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function server($key = '', $default = null, $filter = null)
    {
        if (func_num_args() == 0 || is_null($key)) return $_SERVER;
        return Arr::get($_SERVER, $key, $default, $filter);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 12:29
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    function session($key = '', $default = null, $filter = null)
    {
        if (!isset($_SESSION) || $_SESSION == null) {
            session_start();
        }
        return Arr::get($_SESSION,$key, $default, $filter);
    }

    /**
     * @param bool $key
     * @param null $default
     * @param null $filter
     * @return array|bool|mixed|string
     * itwri 2019/12/5 0:31
     */
    function input($key = false, $default = null,$filter=null){
        if(is_string($key)){
            $data = $this->getInput()->getData(true);
            return Arr::get($data,$key,$default,$filter);
        }elseif($key == true){
            return $this->getInput()->getData(true);
        }
        return $this->getInput()->getData(false);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 11:02
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return array|bool|mixed|string
     */
    function param($key = '', $default = null, $filter = null)
    {
        if(func_num_args()==0){
            return $this->all();
        }
        /**
         * 如果不传参数，返回Input的data
         */
        if (func_num_args() == 0 || empty($key)) {
            $input_data = $this->Input->getData(true);
            $get_data = $this->get();
            return array_merge($this->extraData, $get_data, $input_data);
        }

        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            $method = substr($key, 0, $pos);
            if (in_array($method, ['get', 'post', 'session', 'cookie', 'server', 'file'])) {
                $key = substr($key, $pos + 1);
                return call_user_func_array([$this, $method], [$key, $default, $filter]);
            }
        }

        /**
         * 优先从Input 的data里取值
         */
        $data = $this->all();


        return Arr::get($data, $key, $default, $filter);
    }

    /**
     * @return array
     * itwri 2019/8/5 2:16
     */
    function all(){
        return array_merge($this->extraData,$this->get(),$this->post(),$this->Input->getData(1));
    }

    /**
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     * itwri 2019/12/5 1:23
     */
    function header($key = '', $default = null, $filter = null){
        return call_user_func_array([$this->getHeader(),'get'],func_get_args());
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 14:41
     *
     * @return mixed|string
     */
    function getAction()
    {
        if (empty($this->action)) {
            $this->action = $this->get($this->config('var_action'), $this->config('default_action', 'index'));
        }
        return $this->action;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 14:41
     *
     * @return mixed|string
     */
    function getController()
    {
        if (empty($this->controller)) {
            $name = $this->get($this->config('var_controller'), $this->config('default_controller', 'index'));
            $name = str_replace(['/', '.'], '\\', $name);
            $array = explode('\\', $name);

            $class = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, array_pop($array));

            $path = $array ? implode('\\', $array) . '\\' : '';
            $this->controller = $path . $class;
        }

        return $this->controller;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 14:41
     *
     * @return mixed|string
     */
    function getModule()
    {
        if (empty($this->module)) {
            $this->module = $this->get($this->config('var_module'), $this->config('default_module', 'index'));
        }
        return $this->module;
    }

    /**
     * @return Header|null
     */
    function getHeader()
    {
        return $this->Header;
    }

    /**
     * @return Post|null
     * itwri 2019/8/9 11:03
     */
    function getPost(){
        return $this->Post;
    }

    /**
     * @return Get|null
     * itwri 2019/8/9 11:03
     */
    function getGet(){
        return $this->Get;
    }

    /**
     * @return Input|null
     * itwri 2019/8/9 11:04
     */
    function getInput(){
        return $this->Input;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 18:47
     *
     * @param string $key
     * @param null $default
     * @return array|mixed|string
     */
    function config($key = '', $default = null)
    {
        if (func_num_args() == 0 || empty($key)) {
            return $this->config;
        }
        return Arr::get($this->config, $key, $default);
    }

    /**
     * 当前的请求类型
     * @access public
     * @param  bool $origin 是否获取原始请求类型
     * @return string
     */
    public function getMethod($origin = false)
    {
        if ($origin) {
            // 获取原始请求类型
            return $this->server('REQUEST_METHOD', 'GET');
        } elseif (!$this->_method) {
            if ($_method = $this->param($this->config['var_method'])) {
                $this->_method = strtoupper($_method);
            } elseif ($_method = $this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->_method = strtoupper($_method);
            } else {
                $this->_method = $this->server('REQUEST_METHOD', 'GET');
            }
        }

        return $this->_method;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 13:09
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function getScheme()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 10:56
     *
     * @return mixed
     */
    public function getDomain()
    {
        return explode(':', $this->server('HTTP_HOST', ''))[0];
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 11:04
     *
     * @return int
     */
    public function getPort()
    {
        $arr = explode(':', $this->getHost());
        return isset($arr[1]) && !in_array($arr[1], ['80', '443']) ? $arr[1] : '';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 13:25
     *
     * @return mixed
     */
    public function getScriptName()
    {
        return $this->server('SCRIPT_NAME', '');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 11:09
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->server('REQUEST_URI', '');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 11:33
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getScheme() . "://" . $this->getHost() . $this->getUri();
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/31
     * Time: 20:37
     *
     * @return string
     */
    public function getRootUrl()
    {
        return $this->getScheme() . "://" . $this->getHost();
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 13:18
     *
     * @return string
     */
    public function getRawUrl()
    {
        return $this->getRootUrl() . $this->getScriptName() . ($this->getRawQuery() ? "?" . $this->getRawQuery() : '');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 11:12
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->Url->getQuery();
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 13:05
     *
     * @return mixed
     */
    public function getRawQuery()
    {
        return $this->server('QUERY_STRING', '');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 13:27
     *
     * @param string $key
     * @param null $default
     * @param null $filter
     * @return mixed
     */
    public function getRawParam($key = '', $default = null, $filter = null)
    {
        parse_str($this->getRawQuery(), $params);
        if (func_num_args() == 0) {
            return $params;
        }
        return Arr::get($params, $key, $default, $filter);
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        if (in_array($this->server('HTTPS', ''), ['1', 'on'])
            || 'https' == $this->server('REQUEST_SCHEME')
            || '443' == $this->server('SERVER_PORT')
            || 'https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }

        return false;
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax($ajax = false)
    {
        $value = $this->server('HTTP_X_REQUESTED_WITH', '');
        $result = 'xmlhttprequest' == strtolower($value) ? true : false;

        if (true === $ajax) {
            return $result;
        }
        return $this->get($this->config('var_ajax')) ? true : $result;
    }

    /**
     * 当前是否Pjax请求
     * @access public
     * @param  bool $pjax true 获取原始pjax请求
     * @return bool
     */
    public function isPjax($pjax = false)
    {
        $result = !is_null($this->server('HTTP_X_PJAX')) ? true : false;

        if (true === $pjax) {
            return $result;
        }

        $result = $this->param($this->config['var_pjax']) ? true : $result;
        return $result;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 1:37
     *
     * @return bool
     */
    function isPost()
    {
        return $this->getMethod() == 'POST';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 1:38
     *
     * @return bool
     */
    function isGet()
    {
        return $this->getMethod() == 'GET';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 1:38
     *
     * @return bool
     */
    function isPut()
    {
        return $this->getMethod() == 'PUT';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 1:39
     *
     * @return bool
     */
    function isDelete()
    {
        return $this->getMethod() == 'DELETE';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 2:28
     *
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->Header->getContentType();

        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 17:52
     *
     * @return bool
     */
    function isJson()
    {
        return false !== strpos($this->contentType(), 'application/json');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 17:52
     *
     * @return bool
     */
    function isXml()
    {
        return false !== strpos($this->contentType(), 'text/xml');
    }


    /**
     * 获取客户端IP地址
     * @access public
     * @param  integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param  boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = true)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;

        if (null !== $ip) {
            return $ip[$type];
        }

        $httpAgentIp = $this->config['http_agent_ip'];

        if ($httpAgentIp && $this->server($httpAgentIp)) {
            $ip = $this->server($httpAgentIp);
        } elseif ($adv) {
            if ($this->server('HTTP_X_FORWARDED_FOR')) {
                $arr = explode(',', $this->server('HTTP_X_FORWARDED_FOR'));
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif ($this->server('HTTP_CLIENT_IP')) {
                $ip = $this->server('HTTP_CLIENT_IP');
            } elseif ($this->server('REMOTE_ADDR')) {
                $ip = $this->server('REMOTE_ADDR');
            }
        } elseif ($this->server('REMOTE_ADDR')) {
            $ip = $this->server('REMOTE_ADDR');
        }

        // IP地址类型
        $ip_mode = (strpos($ip, ':') === false) ? 'ipv4' : 'ipv6';

        // IP地址合法验证
        if (filter_var($ip, FILTER_VALIDATE_IP) !== $ip) {
            $ip = ('ipv4' === $ip_mode) ? '0.0.0.0' : '::';
        }

        // 如果是ipv4地址，则直接使用ip2long返回int类型ip；如果是ipv6地址，暂时不支持，直接返回0
        $long_ip = ('ipv4' === $ip_mode) ? sprintf("%u", ip2long($ip)) : 0;

        $ip = [$ip, $long_ip];

        return $ip[$type];
    }

}