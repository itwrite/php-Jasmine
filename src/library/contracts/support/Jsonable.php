<?php /** @noinspection ALL */

/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/26
 * Time: 21:06
 */

namespace Jasmine\library\contracts\support;


interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}