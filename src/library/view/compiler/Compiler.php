<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/1/1
 * Time: 22:49
 */

namespace Jasmine\library\view\compiler;
use Jasmine\library\view\compiler\interfaces\CompilerInterface;
use Jasmine\library\view\compiler\traits\CompileComments;
use Jasmine\library\view\compiler\traits\CompileConditions;
use Jasmine\library\view\compiler\traits\CompileEchos;
use Jasmine\library\view\compiler\traits\CompileLoops;
use Jasmine\library\view\compiler\traits\CompileStatements;
require_once('traits/Properties.php');
require_once('traits/CompileConditions.php');
require_once('traits/CompileEchos.php');
require_once('traits/CompileLoops.php');
require_once('traits/CompileStatements.php');
require_once('traits/CompileComments.php');
require_once('interfaces/CompilerInterface.php');
/**
 * 负责将模板内容解析成合法的PHP代码;
 * Class Compiler
 * @package Jasmine\Library\View
 */
class Compiler implements CompilerInterface {

    /**
     * All of the registered extensions.
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    protected $compilers = [
        'Comments',
        'Extensions',
        'Statements',
        'Echos',
    ];

    /**
     *
     * User: Peter
     * Date: 2019/4/1
     * Time: 21:00
     *
     * @param $extension
     * @return $this
     */
    public function extend($extension){

        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string  $expression
     * @return string
     */
    public function stripParentheses($expression)
    {
        if (substr($expression, 0, 1) === '(') {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Compile the given Blade template contents.
     *
     * @param  string  $value
     * @return string
     */
    public function compileString($value)
    {
        $result = '';
        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (token_get_all($value) as $token)
        {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        return $result;
    }

    /**
     * Parse the tokens from the template.
     *
     * @param  array  $token
     * @return string
     */
    protected function parseToken($token)
    {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML)
        {
            foreach ($this->compilers as $type)
            {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Execute the user defined extensions.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $extension)
        {
            $value = call_user_func_array($extension, array($value, $this));

        }
        return $value;
    }

    use CompileLoops,CompileEchos,CompileConditions,CompileStatements,CompileComments;
}
