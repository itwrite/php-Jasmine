<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/8
 * Time: 2:36
 */

namespace Jasmine\library\http\request\method;

use Jasmine\library\http\schema\Eloquent;

class Post extends Eloquent
{
    function __construct()
    {
        $this->data = &$_POST;
    }
}