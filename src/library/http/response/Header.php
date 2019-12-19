<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/3/20
 * Time: 14:54
 */

namespace Jasmine\library\http\response;


use Jasmine\util\Arr;

class Header
{
    protected $data = [];

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 14:08
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    function get($name, $default = null)
    {
        return Arr::get($this->data, $name, $default);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 13:28
     *
     * @param $name
     * @param $value
     * @return $this
     */
    function set($name, $value)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
            return $this;
        }

        if (!empty($name) && !empty($value)) {
            $this->data[$name] = $value;
        }
        return $this;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 13:19
     *
     * @param $name
     * @param string $value
     * @return $this
     */
    function send($name = '', $value = '')
    {

        /**
         * set data
         */
        func_num_args()>0 && call_user_func_array([$this, 'set'], func_get_args());

        foreach ($this->data as $key => $val) {
            if(!is_null($val)){
                header($key . ':' . $val);
            }
        }
        return $this;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/21
     * Time: 14:49
     *
     * @return array
     */
    function getData(){
        return $this->data;
    }
}