<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/1
 * Time: 13:45
 */

namespace Jasmine\library\validate;


class Validate
{
    protected $messages = [];
    protected $rules = [];
    protected $scenes = [];

    private $_errorMsgArr = [];
    private $_extensions = [];
    private $_extensionsMsg = [];

    function __construct(array $rules = [], array $messages = [])
    {
        $this->rules = array_merge($this->rules, $rules);
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * @param array $rules
     * @param array $messages
     * @return Validate
     * itwri 2019/8/18 12:53
     */
    static function make(array $rules = [], array $messages = [])
    {
        $static = new static($rules, $messages);
        return $static;
    }

    /**
     * @param $field
     * @param $rule
     * @param $args
     * @return $this
     * itwri 2019/8/1 23:08
     */
    private function _error($field, $rule, $args = [])
    {
        //组装key
        $key = implode('.', [$field, $rule]);

        //消息判空
        $message = isset($this->messages[$key]) ? $this->messages[$key] : '';

        /**
         * 如果有扩展的提示消息
         */
        if (isset($this->_extensionsMsg[$key])) {
            if (is_callable($this->_extensionsMsg[$key])) {
                $message = call_user_func_array($this->_extensionsMsg[$key], $args);
            } elseif (is_string($this->_extensionsMsg[$key])) {
                $message = $this->_extensionsMsg[$key];
            }
        }

        //消息中替换个数
        $count = substr_count($message, '%s');

        $args = $args > $count ? array_slice($args, 0, $count) : $args;

        array_unshift($args, $message);

        $this->_errorMsgArr[] = call_user_func_array('sprintf', $args);
        return $this;
    }

    /**
     * @param $field
     * @param $rule
     * @param null $ruleFun
     * @param null $msg
     * @return $this
     * itwri 2019/8/16 15:29
     */
    function addRule($field, $rule, $ruleFun = null, $msg = null)
    {

        /**
         * 检查是否已有规则
         */
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = '';
        }

        /**
         * 判断新规则是否合法
         */
        if (!is_string($rule) || empty($rule) || func_num_args() < 2) {
            return $this;
        }

        /**
         * 分析已有规则
         */
        $arr = explode('|', $this->rules[$field]);

        /**
         * 解析新规则
         */
        $method = implode('.', [$field, explode(':', $rule)[0]]);

        if (func_num_args() > 2 && is_callable($ruleFun) && !isset($this->_extensions[$method])) {
            $this->_extensions[$method] = $ruleFun;
        }

        /**
         *
         */
        if (is_string($msg) || (is_callable($msg) && !isset($this->_extensionsMsg[$method]))) {
            $this->_extensionsMsg[$method] = $msg;
        }

        /**
         * 追加到已有的规则中
         */
        $arr[] = $rule;

        $this->rules[$field] = implode('|', $arr);

        return $this;
    }

    /**
     * @return string
     * itwri 2019/8/1 18:39
     */
    public function getError()
    {
        return implode(',', $this->_errorMsgArr);
    }

    /**
     * @param $scene
     * @param array $data
     * @return bool
     * itwri 2019/8/10 0:24
     */
    function check($scene, Array $data = [])
    {
        if(func_num_args()==0){
            $data = array_merge($_GET, $_POST);
            $needCheckFields = array_keys($this->rules);
        }else if(func_num_args() == 1){
            $data = $scene;
            $needCheckFields = array_keys($this->rules);
        }else{
            if (is_array($scene)) {
                $needCheckFields = array_values($scene);
            } else if (is_string($scene) && strpos($scene, ',') !== false) {
                $needCheckFields = explode(',', $scene);
            } else {
                /**
                 * 加载场景，分析场景字段
                 */
                $needCheckFields = isset($this->scenes[$scene]) ? $this->scenes[$scene] : [];
            }
        }

        return $this->checkFields($needCheckFields, $data);
    }

    /**
     * 获取数组值
     * @param $target
     * @param $key
     * @param null $default
     * @return mixed
     * itwri 2019/8/23 0:51
     */
    protected function getValue($target, $key, $default = null)
    {
        if (func_num_args() < 2 || is_null($key)) return $target;

        if (is_string($key)) {

            if (is_array($target) && isset($target[$key])) return $target[$key];

            foreach (explode('.', $key) as $segment) {
                if (is_array($target)) {
                    if (!array_key_exists($segment, $target)) {
                        return self::value($default);
                    }

                    $target = $target[$segment];
                } elseif (is_object($target)) {
                    if (!isset($target->{$segment})) {
                        return self::value($default);
                    }

                    $target = $target->{$segment};
                } else {
                    return self::value($default);
                }
            }
        }
        return self::value($target);
    }


    /**
     * @param $needCheckFields
     * @param array $data
     * @return bool
     * itwri 2019/8/10 0:21
     */
    protected function checkFields(array $needCheckFields, Array $data = null)
    {
        /**
         * 没有需要检查的字段
         */
        if (empty($needCheckFields)) {
            return true;
        }

        foreach ($needCheckFields as $field) {
            //如果字段为空，跳过
            if (empty($field)) continue;

            $field = trim($field);
            /**
             * 分析字段，如果有规则存在，则根据规则进行校验
             */
            $rules = trim(isset($this->rules[$field]) ? $this->rules[$field] : '');
            if (!empty($rules)) {
                /**
                 * 可以多规则，以英文字符 '|' 间隔
                 */
                $rules = is_array($rules) ? $rules : explode('|', $rules);

                /**
                 * 对每一个规则进行检查
                 */
                foreach ($rules as $rule) {
                    /**
                     * 英文‘:’之后的为参数，参数以英文‘,’间隔
                     */
                    $arr = explode(':', $rule);
                    /**
                     * 第一个为规则方法名
                     */
                    $rule = array_shift($arr);

                    /**
                     * 处理传参数据
                     */
                    $args = explode(',', implode(':', $arr));

                    /**
                     * 数据值
                     */
                    $value = $this->getValue($data, $field);


                    //处理需要的传参
                    $params = array_merge([$value], $args);
                    //
                    $method = implode('.', [$field, $rule]);
                    /**
                     * 自定义的扩展优先
                     */
                    if (isset($this->_extensions[$method]) && is_callable($this->_extensions[$method])) {
                        /**
                         * 校验如果不正确则返回false，退出校验
                         */
                        $res = call_user_func_array($this->_extensions[$method], $params);
                        if ($res != true) {
                            //生成错和提示
                            $this->_error($field, $rule, $args);
                            return false;
                        }
                    } else {
                        /**
                         * 内部方法，方法名加前缀,转驼峰
                         */
                        $method = 'is' . $this->studly($rule);

                        /**
                         * 如果存在规则方法才去校验
                         */
                        if (!empty($method) && method_exists($this, $method)) {

                            /**
                             * 校验如果不正确则返回false，退出校验
                             */
                            $res = call_user_func_array([$this, $method], $params);
                            if ($res != true) {
                                //生成错和提示
                                $this->_error($field, $rule, $args);
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $value
     * @return mixed
     * itwri 2019/8/9 22:36
     */
    protected function studly($value)
    {
        $this->value($value);

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * @param $value
     * @return mixed
     * itwri 2019/8/9 22:36
     */
    protected function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * =================================================================================
     * 规则 以 rule 为前缀的方法
     * =================================================================================
     */
    /**
     * 必要的
     * @param $value
     * @return bool
     * itwri 2019/8/1 18:37
     */
    function isRequire($value)
    {
        $value = is_string($value) ? trim($value) : $value;
        if($value === 0 || $value == '0'){
            return true;
        }
        if (!is_null($value)) {
            return !empty($value);
        }
        return false;
    }

    /**
     * 在一定的长度之间
     * @param string $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/1 22:49
     */
    function isLength($value, $min, $max = null)
    {
        $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if (func_num_args() < 3) {
            return $len == $min;
        }
        $bok = false;
        $min != '' && $bok = $len >= $min;
        $max != '' && $bok = $len < $max;
        return $bok;
    }

    /**
     * 在一个特定和枚举列表中
     * @param $value
     * @return bool
     * itwri 2019/8/1 23:34
     */
    function isIn($value)
    {
        return in_array($value, array_slice(func_get_args(), 1));
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:13
     */
    function isNotIn($value)
    {
        return !call_user_func_array([$this, 'isIn'], func_get_args());
    }

    /**
     * 是个数字
     * @param $value
     * @return bool
     * itwri 2019/8/3 16:33
     */
    function isNumber($value)
    {
        return is_numeric($value);
    }

    /**
     * 是个整数
     * @param $value
     * @return bool
     * itwri 2019/8/3 21:32
     */
    function isInt($value)
    {
        return $this->isNumber($value) && strpos($value, '.') == false;
    }

    /**
     * 是个正整数
     * @param $value
     * @return bool
     * itwri 2019/8/4 22:09
     */
    function isInteger($value)
    {
        return $this->isInt($value) && $value >= 0;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:52
     */
    function isFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * 在一定范围内
     * @param $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/4 22:12
     */
    function isBetween($value, $min, $max)
    {
        if ($this->isNumber($value) && func_num_args() > 2) {
            return $value >= $min && $value <= $max;
        }
        return false;
    }

    /**
     * @param $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/27 0:51
     */
    function isRange($value, $min, $max)
    {
        return $this->isBetween($value, $min, $max);
    }

    /**
     * @param $value
     * @param $val
     * @return bool
     * itwri 2019/8/23 0:46
     */
    function isEq($value, $val)
    {
        return $value == $val;
    }

    /**
     * @param $value
     * @param $val
     * @return bool
     * itwri 2019/8/27 0:36
     */
    function isDiff($value, $val)
    {
        return !call_user_func_array([$this, 'isEq'], func_get_args());
    }

    /**
     * @return bool
     * itwri 2019/8/27 0:36
     */
    function isDifferent()
    {
        return !call_user_func_array([$this, 'isDiff'], func_get_args());
    }

    /**
     *
     * @param $value
     * @param $min
     * @return bool
     * itwri 2019/8/16 13:12
     */
    function isMin($value, $min)
    {
        return $value >= $min;
    }

    /**
     * @param $value
     * @param $max
     * @return bool
     * itwri 2019/8/16 14:09
     */
    function isMax($value, $max)
    {
        return $value <= $max;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/16 14:11
     */
    function isEmail($value)
    {
        if (!$value) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function isBool($value)
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }


    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function isBoolean($value)
    {
        return $this->isBool($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:37
     */
    function isDate($value)
    {
        return strtotime($value) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:42
     */
    function isArray($value)
    {
        return is_array($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:50
     */
    function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $value
     * @param int $type
     * @return bool
     * itwri 2019/9/6 1:11
     */
    function isIp($value, $type = 4)
    {
        $flag = $type == 6 ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4;
        return filter_var($value, FILTER_VALIDATE_IP, $flag) !== false;
    }

    /**
     * @param $value
     * @param $rule
     * @return false|int
     * itwri 2019/9/15 21:19
     */
    function isRegExp($value,$rule){
        $args = func_get_args();
        $value = array_shift($args);
        $pattern = implode('',$args);
        return preg_match($pattern,$value);
    }
}