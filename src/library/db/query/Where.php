<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 11:56
 */

namespace Jasmine\library\db\query;

use Jasmine\library\db\query\capsule\Condition;
use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\db\query\schema\Eloquent;

class Where extends Eloquent{

    /**
     * @var string
     */
    private $_boolean = 'and';

    /**
     * @return string
     */
    function getBoolean(){
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

    /**
     * the data stores string or Expression or Condition or Where
     * @param $field
     * @param string $operator
     * @param string $value
     * @param string $boolean
     * @return $this
     */
    public function where($field, $operator = '', $value = '', $boolean = 'and')
    {
        if($field instanceof Expression){
            $this->data[] = $field;
        }
        if (is_string($field) && strlen($field) > 0) {
            if (func_num_args() == 1) {
                $this->data[] = $field;
            }
            elseif (func_num_args() == 2) {
                /**
                 * 参数只有两个时的情况
                 * 第一个必为field,
                 * 第二个分两种情况
                 * 1,当它为数组时，使用in
                 * 2,当它为其它时，使用=
                 */
                if (is_array($operator)) {
                    list($value,$operator) = array($operator,'in');
                    $this->data[] = new Condition($field, $operator, $value, $boolean);
                } else {
                    list($value,$operator) = array($operator,'=');
                    $this->data[] = new Condition($field, $operator, $value, $boolean);
                }
            } elseif (func_num_args() > 2) {
                $this->data[] = new Condition($field, $operator, $value, $boolean);
            }
        } elseif (is_array($field)) {
            $boolean = $operator;
            foreach ($field as $f => $v) {
                $this->where($f, '=', $v, $boolean);
            }
        } elseif ($field instanceof \Closure) {
            $boolean = $operator;
            $whereObj = new self();
            $whereObj->setBoolean($boolean);
            $result = call_user_func($field, $whereObj);
            $this->data[] = isset($result) ? $result : $whereObj;
        }
        return $this;
    }
}