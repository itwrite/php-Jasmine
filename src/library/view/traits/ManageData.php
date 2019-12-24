<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/21
 * Time: 0:26
 */

namespace Jasmine\library\view\traits;


trait ManageData
{
    protected static $data = [];

    private $_data = [];

    protected $dataPublic = true;

    public function arrSet(&$array, $key, $value)
    {
        if (is_null($key)) {
            $array = $value;
            return $this;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->arrSet($array, $k, $v);
            }
            return $this;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }
        if (is_null($value)) {
            unset($array[array_shift($keys)]);
        } else {
            $array[array_shift($keys)] = $value;
        }

        return $this;
    }

    /**
     * @param $key
     * @param string $value
     * @return $this
     */
    public function assign($key, $value = '')
    {
        //the key is null, do nothing
        if (is_null($key)) return $this;

        if ($this->dataPublic) {
            $this->arrSet(self::$data, $key, $value);
        } else {
            $this->arrSet($this->_data, $key, $value);
        }

        return $this;
    }

    /**
     * @param bool $public
     * @return $this
     */
    protected function setDataPublic($public = true){
        $this->dataPublic = $public == true;
        return $this;
    }
}