<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/1/2
 * Time: 21:33
 */

namespace Jasmine\library\view\compiler\traits;


trait CompileConditions {
    /**
     * Compile the unless statements into valid PHP.
     * @ unless
     * @param string  $expression
     * @return string
     */
    protected function compileUnless($expression)
    {
        return "<?php if (!$expression): ?>";
    }

    /**
     * Compile the end unless statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileEndunless($expression)
    {
        return "<?php endif; ?>";
    }

    /** =================================== if/elseif/else/endif============================================================ */
    /**
     * Compile the if statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileElse($expression)
    {
        return "<?php else: ?>";
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileEndif($expression)
    {
        return "<?php endif; ?>";
    }
}