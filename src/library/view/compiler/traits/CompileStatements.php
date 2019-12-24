<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/1/3
 * Time: 11:52
 */

namespace Jasmine\library\view\compiler\traits;


trait CompileStatements {

    /**
     * Compile Blade statements that start with "@".
     *
     * @param  string  $value
     * @return string
     */
    protected function compileStatements($value)
    {
        /**
         * /\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x
         * /\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x
         */
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) {
            return $this->compileStatement($match);
        }, $value
        );
    }

    /**
     * Compile a single Blade @ statement.
     *
     * @param  array  $match
     * @return string
     */
    protected function compileStatement($match)
    {
        if (strpos($match[1], '@') !== false) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        }elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method(isset($match[3])?$match[3]:null);
        }
        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }
}