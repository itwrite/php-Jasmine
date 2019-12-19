<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 13:47
 */

namespace Jasmine\library\db\query;

use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\db\query\schema\Eloquent;

class Select extends Eloquent{

    public $distinct = false;

    /**
     * @param bool $distinct
     * @return $this
     */
    public function distinct($distinct = false)
    {
        $this->distinct = $distinct == true;
        return $this;
    }

    /**
     * the data stores string or Expression
     * @param $fields
     * @return $this
     */
    function fields($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $val) {
                $this->fields($val);
            }
        }elseif(is_string($fields) || $fields instanceof Expression){
            $this->data[] = $fields;
        }elseif($fields instanceof \Closure){
            $sql = call_user_func($fields);
            $this->data[] = isset($sql)?(($sql instanceof Expression)?$sql:new Expression($sql)):'';
        }
        return $this;
    }
}