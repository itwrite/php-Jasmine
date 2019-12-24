<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/1/2
 * Time: 21:37
 */

namespace Jasmine\library\view\compiler\traits;


trait CompileLoops {

    protected function compileContinue($expression){
        return "<?php if{$expression}{continue;}";
    }
    /**
     * Compile the for statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileEndfor($expression)
    {
        return "<?php endfor; ?>";
    }

    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileForeach($expression)
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileEndforeach($expression)
    {
        return "<?php endforeach; ?>";
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    protected function compileBreak($expression){
        return "<?php if{$expression}{break;} ?>";
    }
    /**
     * Compile the end-while statements into valid PHP.
     *
     * @param string  $expression
     * @return string
     */
    protected function compileEndwhile($expression)
    {
        return "<?php endwhile; ?>";
    }
}