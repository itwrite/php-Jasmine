<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 14:50
 */

namespace Jasmine\library\db\query;

use Jasmine\library\db\query\schema\Eloquent;

class Set extends Eloquent{
    /**
     * @param $field
     * @param string $value
     * @return $this
     */
    function set($field, $value = '')
    {
        if (is_array($field)) {
            foreach ($field as $f => $v) {
                $this->set($f, $v);
            }
        } elseif (is_string($field) && strlen($field) > 0) {

            if ($value instanceof \Closure) {
                $value = call_user_func($value);
                $this->data[$field] = isset($value)?$value:'';
            } else {
                $this->data[$field] = $value;
            }
        }
        return $this;
    }
}