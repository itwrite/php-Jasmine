<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/3/1
 * Time: 23:34
 */

namespace Jasmine\library\contracts\support;


interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render();
}