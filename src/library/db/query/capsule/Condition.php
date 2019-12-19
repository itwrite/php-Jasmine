<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 10:54
 */

namespace Jasmine\library\db\query\capsule;


class Condition
{
    /**
     * @var string
     */
    private $_field = '';

    /**
     * @var string
     */
    private $_value = '';

    /**
     * @var string
     */
    private $_operator = '=';

    /**
     * @var string
     */
    private $_boolean = 'and';

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @param string $boolean
     */
    function __construct($field, $operator, $value, $boolean = 'and')
    {
        $this->setField($field);
        $this->setOperator($operator);
        $this->setValue($value);
        $this->setBoolean($boolean);
    }

    /**
     * @return string
     */
    function getField()
    {
        return $this->_field;
    }

    /**
     * @param $field
     * @return $this
     */
    function setField($field)
    {
        if (is_string($field)) {
            $this->_field = $field;
        }
        return $this;
    }

    /**
     * @return string
     */
    function getValue()
    {
        return $this->_value;
    }

    /**
     * @param $value
     * @return $this
     */
    function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * @return string
     */
    function getOperator()
    {
        return $this->_operator;
    }

    /**
     * @param string $operator
     * @return $this
     */
    function setOperator($operator = '=')
    {
        if (is_string($operator)) {
            $this->_operator = preg_replace('/\s+/', ' ', strtolower(trim($operator)));
        }
        return $this;
    }

    /**
     * @return string
     */
    function getBoolean()
    {
        return $this->_boolean;
    }

    /**
     * @param string $boolean
     * @return $this
     */
    function setBoolean($boolean = 'and')
    {
        $boolean = strtolower($boolean);
        if (in_array($boolean, array('and', 'or'))) {
            $this->_boolean = $boolean;
        }
        return $this;
    }
}