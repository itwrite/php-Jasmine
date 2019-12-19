<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/8
 * Time: 2:31
 */

namespace Jasmine\library\http\request\method;

use Jasmine\library\http\schema\Eloquent;

class Get extends Eloquent
{
    function __construct($url='')
    {
        if(func_num_args()>0 && is_string($url) &&!empty($url)){
            $data = parse_url($url);
            parse_str(isset($data['query'])?$data['query']:'',$this->data);
        }else{
            $this->data = &$_GET;
        }
    }
}