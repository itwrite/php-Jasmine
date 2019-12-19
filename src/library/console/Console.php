<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2019/1/16
 * Time: 11:21
 */

namespace Jasmine\library\console;


class Console
{
    /**
     * @var Input|null
     */
    protected $Input = null;

    /**
     * @var Output|null
     */
    protected $Output = null;

    function __construct()
    {
        $this->Input = new Input();

        $this->Output = new Output();
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/1/16
     * Time: 11:26
     *
     * @return Input|null
     */
    function getInput(){
        return $this->Input;
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/1/16
     * Time: 11:26
     *
     * @return Output|null
     */
    function getOutput(){
        return $this->Output;
    }
}