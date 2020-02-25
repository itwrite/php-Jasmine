<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/26
 * Time: 16:45
 */

namespace app\index\command;


use Jasmine\App;

class Test
{
    function index(){

        App::init()->getDb()->table('aq_users_ips')->select();
        echo 'test here.';
    }
}