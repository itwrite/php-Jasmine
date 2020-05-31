<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/1
 * Time: 13:45
 */

namespace Jasmine\library\validate;


use Jasmine\library\validate\interfaces\ValidatorInterface;

class Validator implements ValidatorInterface
{
    //存放提示信息
    protected $messages = [];

    /**
     * 规则
     * @var array
     */
    protected $rules = [
        self::SCENE_CREATE => [],
        self::SCENE_UPDATE => [],
        self::SCENE_DELETE => [],
        self::SCENE_LIST   => [],
    ];

    /**
     * 暂存需要验证的数据
     * @var array
     */
    private $_data = [];

    /**
     * 错误信息暂存区
     * @var array
     */
    private $_errorMsgArr = [];

    /**
     * 扩展规则
     * @var array
     */
    private $_extensions = [];

    /**
     * 扩展提示信息
     * @var array
     */
    private $_extensionsMsg = [];

    function __construct()
    {
        //
    }

    /**
     * @param array $data
     * @return $this
     * itwri 2020/5/11 10:26
     */
    public function with(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @param string $scene
     * @param array $rules
     * @return $this
     * itwri 2020/5/11 10:30
     */
    public function setRules(string $scene, array $rules)
    {
        $this->rules[$scene] = $rules;
        return $this;
    }

    /**
     * @param string $scene
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function getRules(string $scene)
    {
        return isset($this->rules[$scene]) ? $this->rules[$scene] : null;
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
     * @param $scene
     * @param $field
     * @param $rule
     * @param null $ruleFun
     * @param null $msg
     * @return $this|mixed
     * itwri 2020/5/11 12:40
     */
    function addRule($scene, $field, $rule, $ruleFun = null, $msg = null)
    {
        if (!isset($this->rules[$scene])) {
            $this->rules[$scene] = [];
        }

        /**
         * 检查是否已有规则
         */
        if (!isset($this->rules[$scene][$field])) {
            $this->rules[$scene][$field] = '';
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
        $arr = explode('|', $this->rules[$scene][$field]);

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

        $this->rules[$scene][$field] = implode('|', $arr);

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
     * @param mixed $scene
     * @return bool
     * itwri 2019/8/10 0:24
     */
    function check($scene)
    {
        /**
         * 规则场景为空（即没有规则）时，可认为不需要校验，直接返回
         */
        if (!isset($scene) || empty($scene)) {
            return true;
        }

        /**
         * 当传入的是一个数组
         * 则可认为传入的是规则的数据,即临时规则
         */
        if (is_array($scene)) {
            $rules = $scene;
        } else {
            $rules = $this->getRules($scene);
        }

        /**
         * 取出规则中所有需要校验的字段
         */
        $baseFields = array_keys($rules);

        return $this->checkFields($rules, $baseFields, $this->_data);
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
        /**
         * 参数小于2或者key为null
         * 返回原数据
         */
        if (func_num_args() < 2 || is_null($key)) return $target;

        /**
         * 字符键、数据键
         */
        if (is_string($key) || is_numeric($key)) {

            /**
             * 如果存在，则直接返回
             */
            if (is_array($target) && isset($target[$key])) return $target[$key];

            /**
             * 按字符‘.’切割分析
             */
            foreach (explode('.', strval($key)) as $segment) {
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
     * @param array $allRules
     * @param array $needCheckFields
     * @param array|null $data
     * @return bool
     * itwri 2020/5/11 20:43
     */
    protected function checkFields($allRules, array $needCheckFields, Array $data = null)
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
            print_r($field."\r\n");
            /**
             * 分析字段，如果有规则存在，则根据规则进行校验
             */
            $rules = trim(isset($allRules[$field]) ? $allRules[$field] : '');

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
                    print_r($value."\r\n");

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
                        $method = 'rule' . $this->studly($rule);

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
    function ruleRequire($value)
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === 0 || $value == '0') {
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
    function ruleLength($value, $min, $max = null)
    {
        $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if (func_num_args() < 3) {
            return $len >= $min;
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
    function ruleIn($value)
    {
        return in_array($value, array_slice(func_get_args(), 1));
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:13
     */
    function ruleNotIn($value)
    {
        return !call_user_func_array([$this, 'isIn'], func_get_args());
    }

    /**
     * 是个数字
     * @param $value
     * @return bool
     * itwri 2019/8/3 16:33
     */
    function ruleNumber($value)
    {
        return is_numeric($value);
    }

    /**
     * 是个整数
     * @param $value
     * @return bool
     * itwri 2019/8/3 21:32
     */
    function ruleInt($value)
    {
        return $this->ruleNumber($value) && strpos($value, '.') == false;
    }

    /**
     * 是个正整数
     * @param $value
     * @return bool
     * itwri 2019/8/4 22:09
     */
    function ruleInteger($value)
    {
        return $this->ruleInt($value) && $value >= 0;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:52
     */
    function ruleFloat($value)
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
    function ruleBetween($value, $min, $max)
    {
        if ($this->ruleNumber($value) && func_num_args() > 2) {
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
    function ruleRange($value, $min, $max)
    {
        return $this->ruleBetween($value, $min, $max);
    }

    /**
     * @param $value
     * @param $val
     * @return bool
     * itwri 2019/8/23 0:46
     */
    function ruleEq($value, $val)
    {
        return $value == $val;
    }

    /**
     * @param $value
     * @param $val
     * @return bool
     * itwri 2019/8/27 0:36
     */
    function ruleDiff($value, $val)
    {
        return !call_user_func_array([$this, 'isEq'], func_get_args());
    }

    /**
     * @return bool
     * itwri 2019/8/27 0:36
     */
    function ruleDifferent()
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
    function ruleMin($value, $min)
    {
        return $value >= $min;
    }

    /**
     * @param $value
     * @param $max
     * @return bool
     * itwri 2019/8/16 14:09
     */
    function ruleMax($value, $max)
    {
        return $value <= $max;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/16 14:11
     */
    function ruleEmail($value)
    {
        if (!$value) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function ruleBool($value)
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }


    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function ruleBoolean($value)
    {
        return $this->ruleBool($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:37
     */
    function ruleDate($value)
    {
        return strtotime($value) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:42
     */
    function ruleArray($value)
    {
        return is_array($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:50
     */
    function ruleUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $value
     * @param int $type
     * @return bool
     * itwri 2019/9/6 1:11
     */
    function ruleIp($value, $type = 4)
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
    function ruleRegExp($value, $rule)
    {
        $args = func_get_args();
        $value = array_shift($args);
        $pattern = implode('', $args);
        return preg_match($pattern, $value);
    }
}