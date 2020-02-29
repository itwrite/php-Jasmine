<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2019/1/5
 * Time: 17:42
 */

namespace Jasmine\library\console;


class Output
{
    /**
     * @param string $str
     * @param int $num
     * @param string $pad
     * @return string
     */
    function pad($str='',$num=20,$pad =' '){
        if(PHP_OS != 'Linux') {
            $str = mb_convert_encoding($str,"UTF-8","GBK");
        }
        return str_pad($str,$num,$pad);
    }

    /**
     * @param string $str
     */
    function e($str=''){
        echo implode('',func_get_args())."\n";
    }

    /**
     * @param $columns
     * @param int $width
     * @param bool $isLine
     * itwri 2019/8/7 18:15
     */
    function tr($columns, $width = 150,$isLine=false)
    {
        $count = count($columns);
        $w = ceil($width / $count);
        $firstChar = $isLine?'+':'|';
        $pad = $isLine?'-':' ';
        $data = [];
        foreach ($columns as $column) {

            if (is_string($column)) {

                $column = (empty($column) || $isLine) ? '' : " ".$column;
                $data[] = $firstChar.self::pad($column, $w, $pad);

            } elseif (is_array($column)) {

                $data[] = $firstChar.self::pad(isset($column['content']) ? $column['content']: '', isset($column['width']) ? $column['width'] : $w,$pad);
            }
        }
        call_user_func_array('self::e', $data);
    }

    /**
     * @param $title
     * @param int $width
     * itwri 2019/9/2 16:50
     */
    function title($title, $width = 150)
    {
        $len = strlen($title);
        $left = ceil($width / 2) - ceil($len / 2) - 5;
        self::e(self::pad(str_pad('', $left, '-') . $title, $width, '-'));
    }

    /**
     * @param array $columns
     * @param int $width
     * itwri 2019/9/2 16:50
     */
    function head($columns = [],  $width = 150){
        self::tr($columns,$width,true);
        self::tr($columns,$width);
        self::tr($columns,$width,true);
    }

    /**
     * @param int $width
     * itwri 2019/9/2 16:52
     */
    function line($width = 150){
        self::tr([],$width,true);
    }
}