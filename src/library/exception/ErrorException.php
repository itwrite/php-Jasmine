<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/12/4
 * Time: 23:02
 */

namespace Jasmine\library\exception;


use Throwable;

class ErrorException extends \Exception
{
    function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}