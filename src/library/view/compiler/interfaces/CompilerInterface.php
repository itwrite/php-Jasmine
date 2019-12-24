<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/17
 * Time: 21:15
 */

namespace Jasmine\library\view\compiler\interfaces;


interface CompilerInterface
{
    function extend($extension);

    function compileString($value);
}