<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/21
 * Time: 1:16
 */

/**
 * @param $path
 * @return string
 * itwri 2019/12/21 1:16
 */

function assets($path){
    //explode the script path
    $arr = explode('/', \Jasmine\helper\Server::get('PHP_SELF', ''));
    //the last one is the filename,so remove it;
    array_pop($arr);

    return implode('/',$arr).'/assets/'.$path;
}