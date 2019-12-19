<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2017/11/15
 * Time: 18:15
 */

namespace Jasmine\library\db\query\schema;

require_once __DIR__.'/../capsule/Expression.php';
use Jasmine\library\db\query\capsule\Expression;

abstract class Eloquent
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $cache = array();


    /**
     * @return array
     */
    function data()
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    function roll()
    {
        /**
         * 推出最后一个
         */
        $data = array_pop($this->cache);
        !is_null($data) && $this->data = $data;

        return $this;
    }

    /**
     * @return $this
     */
    function clear()
    {
        $this->cache[] = $this->data;
        $this->data = array();
        return $this;
    }

    /**
     * 重置数据
     * User: Peter
     * Date: 2019/3/19
     * Time: 11:25
     *
     * @return $this
     */
    function reset(){
        $this->data = [];
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    function value($value){
        if($value instanceof Expression)return $value->getValue();
        return $value;
    }
}