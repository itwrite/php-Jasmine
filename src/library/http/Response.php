<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/7/18
 * Time: 2:33
 */

namespace Jasmine\library\http;


use Jasmine\library\http\response\Header;

class Response
{
    /**
     * @var Header|null
     */
    protected $Header = null;

    /**
     * 要输出的内容
     * @var string
     */
    protected $data = '';

    /**
     * @var bool
     */
    protected $allowCache = false;

    /**
     * @var int
     */
    protected $cacheTime = 3;

    /**
     * @var int
     */
    protected $code = 200;

    function __construct()
    {
        $this->Header = new Header();
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 14:26
     *
     * @param bool $bool
     * @return $this
     */
    function allowCache($bool = true)
    {
        $this->allowCache = $bool;
        return $this;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 14:25
     *
     * @return string
     */
    function getData()
    {
        $data = $this->handleData($this->data);

        if (null !== $data && !is_string($data) && !is_numeric($data) && !is_callable([
                $data,
                '__toString',
            ])
        ) {
            throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($data)));
        }

        return (string)$data;
    }

    /**
     * 设置页面输出内容
     * @access public
     * @param  mixed $data
     * @return $this
     */
    public function setData($data)
    {
        if (null !== $data && !is_string($data) && !is_numeric($data) && !is_callable([
                $data,
                '__toString',
            ])
        ) {
            throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($data)));
        }

        $this->data = (string)$data;

        return $this;
    }

    /**
     * 页面输出类型
     * @access public
     * @param  string $contentType 输出类型
     * @param  string $charset 输出编码
     * @return $this
     */
    public function setContentType($contentType, $charset = 'utf-8')
    {
        $this->Header->set('Content-Type', $contentType . '; charset=' . $charset);
        return $this;
    }


    /**
     * 发送HTTP状态
     * @access public
     * @param  integer $code 状态码
     * @return $this
     */
    public function setCode($code = 200)
    {
        $this->code = $code;

        return $this;
    }


    /**
     * LastModified
     * @access public
     * @param  string $time
     * @return $this
     */
    public function setLastModified($time)
    {
        $this->Header->set('Last-Modified', $time);

        return $this;
    }

    /**
     * Expires
     * @access public
     * @param  string $time
     * @return $this
     */
    public function setExpires($time)
    {
        $this->Header->set('Expires', $time);

        return $this;
    }

    /**
     * ETag
     * @access public
     * @param  string $eTag
     * @return $this
     */
    public function setETag($eTag)
    {
        $this->Header->set('ETag', $eTag);

        return $this;
    }

    /**
     * 页面缓存控制
     * @access public
     * @param  string $cache 缓存设置
     * @return $this
     */
    public function setCacheControl($cache)
    {
        $this->Header->set('Cache-Control', $cache);

        return $this;
    }

    /**
     * 设置页面不做任何缓存
     * @access public
     * @return $this
     */
    public function noCache()
    {
        $this->Header->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->Header->set('Pragma', 'no-cache');

        return $this;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 15:56
     *
     * @param string $name
     * @param string $value
     * @return $this|Header|mixed|null
     */
    function header($name = '', $value = '')
    {
        if (func_num_args() == 0) {
            return $this->Header;
        }
        if (func_num_args() == 1) {
            return $this->Header->get($name);
        }
        $this->Header->set($name, $value);
        return $this;
    }

    /**
     * @return Header|null
     * itwri 2019/7/31 0:06
     */
    function getHeader(){
        return $this->Header;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 16:08
     *
     * @param $data
     * @return mixed
     */
    function handleData($data)
    {
        return $data;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 15:55
     *
     * @return $this
     */
    function clear()
    {
        $this->data = '';
        return $this;
    }

    /**
     * @param $content
     * @return string
     */
    function output($content)
    {
        echo $content;
        return '';
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 15:45
     *
     * @return bool
     */
    function send()
    {
        if (200 == $this->code && $this->allowCache) {
            $this->setCacheControl('max-age=' . $this->cacheTime . ',must-revalidate');
            $this->setLastModified(gmdate('D, d M Y H:i:s') . ' GMT');
            $this->setExpires(gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + $this->cacheTime) . ' GMT');
        }

        if (!headers_sent() && !empty($this->Header->getData())) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            $this->Header->send();
        }
        $content = $this->getData();
        // 处理输出数据
        $this->output($content);

        $this->clear();

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            return fastcgi_finish_request();
        }elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            return static::closeOutputBuffers(0, true);
        }

        return ob_end_flush();
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     * @param int $targetLevel
     * @param bool $flush
     * @return bool
     * @final
     * itwri 2019/8/1 22:14
     */
    protected static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                return ob_end_flush();
            } else {
                return ob_end_clean();
            }
        }
        return false;
    }
}