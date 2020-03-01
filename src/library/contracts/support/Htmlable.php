<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/3/1
 * Time: 23:50
 */

namespace Jasmine\library\contracts\support;


interface Htmlable
{
    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml();
}