<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 14:49
 */

namespace Jasmine\library\db\query;

require_once("capsule/JoinObject.php");
use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\db\query\capsule\JoinObject;
use Jasmine\library\db\query\schema\Eloquent;


class Join extends Eloquent{
    /**
     * @param string $table
     * @param string $on
     * @param string $type
     * @return $this
     */
    function join($table, $on = '', $type = '')
    {
        if(func_num_args()==1 && is_string($table)){
            $table = new Expression($table);
        }
        if($table instanceof Expression){
            $this->data[] = $table;
        }elseif($table instanceof \Closure){
            $sql = call_user_func($table);
            $this->data[] = isset($sql)?(($sql instanceof Expression)?$sql:(string)$sql):'';
        }else{
            if(is_array($table)){
                $table = implode(',',$table);
            }
            $this->data[] = new JoinObject($table,(new Where())->where($on),$type);
        }

        return $this;
    }
}