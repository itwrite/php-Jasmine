<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/17
 * Time: 21:01
 */

namespace Jasmine\library\view\interfaces;


/**
 * 模板解析：
 * Interface TemplateInterface
 * @package Jasmine\library\view\interfaces
 */
interface TemplateInterface
{
    public function make($view,array $data);

    public function compile($value);

    public function render();
}