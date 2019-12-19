<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 14:50
 */

namespace Jasmine\library\db\query;


use Jasmine\library\db\query\schema\Eloquent;

class Having extends Eloquent
{

    /**
     * @var Where|null
     */
    private $_Where = null;

    function __construct()
    {
        $this->_Where = new Where();
    }

    /**
     * @return Where|null
     */
    function getWhere()
    {
        return $this->_Where;
    }

    /**
     * @param $field
     * @param string $operator
     * @param string $value
     * @param string $boolean
     * @return $this
     */
    function having($field, $operator = '', $value = '', $boolean = 'and')
    {
        call_user_func_array(array($this->_Where, 'where'), func_get_args());
        return $this;
    }

    /**
     * @return $this
     */
    function roll()
    {
        $this->_Where->roll();
        return $this;
    }

    /**
     * @return $this
     */
    function clear()
    {
        $this->_Where->clear();
        return $this;
    }

}