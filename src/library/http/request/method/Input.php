<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/8
 * Time: 2:45
 */

namespace Jasmine\library\http\request\method;


class Input
{
    protected $data = '';

    function __construct()
    {
        $this->data = file_get_contents('php://input');
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 2:29
     *
     * @param bool $assoc
     * @return array|bool|mixed|string
     */
    function getData($assoc = false)
    {
        if ($assoc === true || 0 === strpos(trim($this->data), '{')) {
            $data = json_decode($this->data, true);
            return !$data ? [] : $data;
        } elseif ($assoc === true && strpos($this->data, '=')) {
            parse_str($this->data, $data);
            return $data;
        }
        return $assoc ? [] : $this->data;
    }
}