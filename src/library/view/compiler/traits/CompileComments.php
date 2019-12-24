<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 1:05
 */

namespace Jasmine\library\view\compiler\traits;

trait CompileComments
{
    /**
     * Compile Blade comments into valid PHP.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
    }

}