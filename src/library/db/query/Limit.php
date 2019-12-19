<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 14:50
 */

namespace Jasmine\library\db\query;


use Jasmine\library\db\query\schema\Eloquent;

class Limit extends Eloquent{

    /**
     * array(offset,pageSize)
     * @var array
     */
    protected $data = array();

    /**
     * @param $offset
     * @return $this
     */
    function setOffset($offset)
    {
        $offset = intval($offset);
        $this->data[0] = $offset < 0 ? 0 : $offset;
        return $this;
    }

    /**
     * @param int $page_size
     * @return $this
     */
    function setPageSize($page_size = 0)
    {
        !empty($this->data) && $this->data[1] = $page_size;
        return $this;
    }

    /**
     * @return $this
     */
    function clear()
    {
        if (count(array_keys($this->data))>0) {
            $this->cache[] = $this->data;
        }
        $this->data = array();
        return $this;
    }
}