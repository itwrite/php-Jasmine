<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2019/3/4
 * Time: 10:46
 */

namespace Jasmine\library;

use Jasmine\App;
use Jasmine\helper\Config;
use Jasmine\library\db\Database;
use Jasmine\util\Str;

/**
 *
 * @method $this debug($debug = true)
 * @method $this distinct($distinct = true)
 * @method $this join($table, $on = '', $type = '')
 * @method $this leftJoin($table, $on = '')
 * @method $this rightJoin($table, $on = '')
 * @method $this innerJoin($table, $on = '')
 * @method $this where($field, $operator = '', $value = '', $boolean = 'and')
 * @method $this fields($fields = '*')
 *
 * @method $this whereIn($field, Array $values, $boolean = 'and')
 * @method $this whereNotIn($field, Array $values, $boolean = 'and')
 * @method $this whereBetween($field, Array $values, $boolean = 'and')
 * @method $this whereLike($field, $value, $boolean = 'and')
 * @method $this orderBy($field = '')
 * @method $this groupBy($field = '')
 * @method $this having($field, $operator = '', $value = '', $boolean = 'and')
 * @method $this limit($offset = 0, $page_size = 10)
 * @method $this set($field, $value = '')
 * @method int|false setInc($field, $inc = 1)
 * @method int|false setDec($field, $inc = 1)
 * @method $this roll($option = '')
 *
 * @method int insert(Array $data = [], $is_replace = false)
 * @method bool insertAll(Array $data, $size = 1000, \Closure $closure = null)
 * @method int delete()
 * @method int update(Array $data = [])
 * @method int count()
 * @method array paginator($page = 1, $pageSize = 10)
 * @method array select($fields = '*', $fetch_type = \PDO::FETCH_ASSOC)
 *
 * @method string getLastSql()
 *
 * @method \PDOStatement|false query($statement)
 * @method \PDOStatement|false exec($statement)
 * Class Model
 */
class Model
{
    private $_db = null;

    protected $pk = 'id';
    protected $table_prefix = "";
    protected $table_name = "";
    protected $table_alias = "";

    function __construct()
    {
        /**
         * 使用框架的pdo连接数据库
         */
        $this->_db = App::init()->getDb();

        $arr = explode('\\', get_class($this));
        $class_name = array_pop($arr);
        unset($arr);

        $this->table_prefix = empty($this->table_prefix) ? Config::get('db.table_prefix', '') : $this->table_prefix;

        $this->table_name = empty($this->table_name) ? $class_name : $this->table_name;

    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/3/4
     * Time: 11:34
     *
     * @return string
     */
    function getTableFullName()
    {
        return implode(' ', [$this->table_prefix . Str::snake($this->table_name), $this->table_alias]);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 11:47
     *
     * @param string $table
     * @return $this
     */
    function table($table)
    {
        $table = preg_replace('/\s+/', ' ', trim($table));
        $arr = explode(' ', $table);
        $this->table_name = $arr[0];
        $this->table_alias = isset($arr[1]) ? $arr[1] : $this->table_alias;
        unset($arr);
        return $this;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 11:23
     *
     * @param string $alias
     * @return $this
     */
    function alias($alias)
    {
        $this->table_alias = $alias;
        return $this;
    }

    /**
     * Desc:
     * User: Peter
     * Date: 2019/3/4
     * Time: 11:34
     *
     * @return Database|null
     */
    function getDb()
    {
        return $this->_db;
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 11:26
     *
     * @return string
     */
    function getPk()
    {
        return $this->pk;
    }

    /**
     * @param int $id
     * @param int $fetch_type
     * @return bool|mixed
     * @throws \Exception
     * itwri 2020/1/10 0:23
     */
    function find($id = 0, $fetch_type = \PDO::FETCH_ASSOC)
    {
        $this->getDb()->getFrom()->clear()->table($this->getTableFullName());
        if (func_num_args() > 0 && (is_string($id) || is_numeric($id))) {
            $this->where($this->getPk(), '=', $id);
        }
        return $this->getDb()->get($fetch_type);
    }


    /**
     * @var array
     */
    protected static $_fields = [];

    /**
     * @param $data
     * @return array
     * itwri 2019/8/2 1:28
     */
    public function filterData($data)
    {
        $result = [];
        if (empty($this->_fields)) {
            $rt = $this->query('desc ' . $this->getTableFullName());
            if ($rt != false) {
                $list = $rt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($list as $item) {
                    self::$_fields[$item['Field']] = $item['Type'];
                }
            }
        }

        if (is_array($data)) {
            $fields = array_keys($this->_fields);
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $result[$field] = $data[$field];
                }
            }
        }

        return $result;
    }

    /**
     * @param $value
     * @return db\query\capsule\Expression
     * itwri 2020/3/10 21:08
     */
    public static function raw($value){
        return (new static())->getDb()->raw($value);
    }

    /**
     *
     * User: Peter
     * Date: 2019/3/19
     * Time: 11:46
     *
     * @param $name
     * @param $arguments
     * @return $this|mixed
     * @throws \ErrorException
     */
    function __call($name, $arguments)
    {
        if (!method_exists($this, $name) && method_exists($this->getDb(), $name)) {
            if (in_array($name, explode(',', 'insert,delete,update,select,paginator,count,getLastSql,query,exec,setInc,setDec'))) {
                $this->getDb()->getFrom()->clear()->table($this->getTableFullName());
                return call_user_func_array([$this->getDb(), $name], $arguments);
            } elseif (in_array($name, explode(',', 'debug,distinct,fields,join,leftJoin,rightJoin,innerJoin,where,whereIn,whereNotIn,whereBetween,whereLike,orderBy,groupBy,limit,having,set,clear'))) {
                call_user_func_array([$this->getDb(), $name], $arguments);
                return $this;
            } else {
                throw new \ErrorException('method is not exists:' . $name);
            }
        }
        return $this;
    }
}