<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 14:49
 */

namespace Jasmine\library\db\query;


use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\db\query\schema\Eloquent;

class Order extends Eloquent{
    /**
     * @param $field
     * @return $this
     */
    function field($field)
    {
        if (is_array($field)) {
            foreach ($field as $val) {
                $this->field($val);
            }
        }elseif(is_string($field) || $field instanceof Expression){
            $this->data[] = $field;
        }elseif($field instanceof \Closure){
            $sql = call_user_func($field);
            $this->data[] = isset($sql)?(($sql instanceof Expression)?$sql:(string)$sql):'';
        }
        return $this;
    }
}