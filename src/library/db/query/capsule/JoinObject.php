<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 15:07
 */

namespace Jasmine\library\db\query\capsule;


class JoinObject
{
    /**
     * @var string
     */
    private $_type = '';

    /**
     * @var string
     */
    private $_table = '';

    /**
     * @var null
     */
    private $_on = null;

    /**
     * @param $type
     * @param $table
     * @param $on
     */
    function __construct($table, $on, $type)
    {
        $this->setType($type);
        $this->setTable($table);
        $this->setOn($on);
    }

    /**
     * @return string
     */
    function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     * @return $this
     */
    function setType($type = '')
    {
        $type = preg_replace('/\s+/', ' ', $type);
        $type = strtoupper(trim($type));
        switch ($type) {
            case 'LEFT':
            case 'LEFT JOIN':
                $type = 'LEFT JOIN';
                break;
            case 'RIGHT':
            case 'RIGHT JOIN':
                $type = 'RIGHT JOIN';
                break;
            case 'INNER':
            case 'INNER JOIN':
                $type = 'INNER JOIN';
                break;
            default:
                $type = 'JOIN';
        }
        $this->_type = $type;
        return $this;
    }

    /**
     * @return string
     */
    function getOn()
    {
        return $this->_on;
    }

    /**
     * @param $on
     * @return $this
     */
    function setOn($on)
    {
        $this->_on = $on;
        return $this;
    }

    /**
     * @return string
     */
    function getTable()
    {
        return $this->_table;
    }

    /**
     * @param string $table
     * @return $this
     */
    function setTable($table)
    {
        $this->_table = $table;
        return $this;
    }
}