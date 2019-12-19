<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/20
 * Time: 13:48
 */

namespace Jasmine\library\http\request;


use Jasmine\library\http\schema\Eloquent;

class Header extends Eloquent
{

    function __construct()
    {
        $copy_server = array(
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5' => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $this->data[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $this->data[$copy_server[$key]] = $value;
            }
        }

        if (!isset($this->data['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $this->data['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $this->data['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $this->data['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:07
     *
     * @return string
     */
    function getCookie(){
        return $this->get('Cookie','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:09
     *
     * @return array|mixed
     */
    function getAcceptLanguage(){
        return $this->get('Accept-Language','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:12
     *
     * @return array|mixed
     */
    function getAcceptEncoding(){
        return $this->get('Accept-Encoding','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:16
     *
     * @return array|mixed
     */
    function getAccept(){
        return $this->get('Accept','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:16
     *
     * @return array|mixed
     */
    function getUserAgent(){
        return $this->get('User-Agent','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:16
     *
     * @return array|mixed
     */
    function getUpgradeInsecureRequests(){
        return $this->get('Upgrade-Insecure-Requests','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:16
     *
     * @return array|mixed
     */
    function getCacheControl(){
        return $this->get('Cache-Control','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:16
     *
     * @return array|mixed
     */
    function getHost(){
        return $this->get('Host','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:06
     *
     * @return array|mixed
     */
    function getContentType(){
        return $this->get('Content-Type','');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/24
     * Time: 12:17
     *
     * @return array|mixed
     */
    function getContentLength(){
        return $this->get('Content-Length','');
    }
}