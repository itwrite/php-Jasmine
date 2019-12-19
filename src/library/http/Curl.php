<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/7/19
 * Time: 18:56
 */

namespace Jasmine\library\http;


class Curl
{
    /**
     * @param $url
     * @param bool $params
     * @param array $headers 请求头
     * @param int $https
     * @return bool|mixed
     */
    static function get($url, $params = false, $headers = array(), $https = 0)
    {
        return self::request($url, $params, $headers, 0, $https);
    }

    /**
     * @param $url
     * @param bool $params
     * @param array $headers 请求头
     * @param int $https
     * @return bool|mixed
     */
    static function post($url, $params = false, $headers = array(), $https = 0)
    {
        return self::request($url, $params, $headers, 1, $https);
    }

    /**
     * @param string $url 请求网址
     * @param bool $params 请求参数
     * @param array $headers 请求头
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function request($url, $params = false, $headers = array(), $ispost = 0, $https = 0)
    {
        $newHeaders = array();
        foreach ($headers as $key => $value) {
            $newHeaders[] = implode(':',[$key,$value]);
        }
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $newHeaders);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            return "cURL Error: " . curl_error($ch);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

}