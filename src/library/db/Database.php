<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 12:47
 */

namespace Jasmine\library\db;


use Jasmine\library\db\connection\capsule\Link;
use Jasmine\library\db\connection\Connection;
use Jasmine\library\db\interfaces\DatabaseInterface;
use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\exception\ErrorException;

require_once 'Builder.php';
require_once 'connection/capsule/Link.php';
require_once 'connection/Connection.php';
require_once 'interfaces/DatabaseInterface.php';
require_once("query/capsule/Expression.php");

class Database extends Builder implements DatabaseInterface
{
    protected $debug = false;
    /**
     * @var Connection|null
     */
    protected $Connection = null;

    /**
     * @var string
     */
    protected $linkName = null;

    /**
     * @var bool
     */
    private $_sticky = false;

    /**
     * @var array
     */
    protected $errorArr = [];

    /**
     * @var array
     */
    protected $logConfig = [
        'directory'=> ''
    ];

    function __construct(array $config)
    {
        parent::__construct();

        /**
         * 如果有日志的配置
         */
        if(isset($config['log']) && is_array($config['log'])){
            $this->logConfig = array_merge( $this->logConfig, $config['log']);
        }
        $this->tablePrefix = isset($config['table_prefix'])?$config['table_prefix']:'';
        $this->Connection = new Connection($config);
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function debug($debug = false)
    {
        $this->debug = $debug == true;
        return $this;
    }

    /**
     * @param $sticky
     * @return $this
     * itwri 2019/12/19 14:33
     */
    public function sticky($sticky){
        $this->_sticky = $sticky == true;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function link($name){
        $this->linkName = $name;
        return $this;
    }

    /**
     * @param null $name
     * @return Link|mixed|null
     * @throws \Exception
     * itwri 2019/12/19 14:18
     */
    protected function getLink($name = null)
    {
        /**
         * 如果为真，使用主连接
         */
        if ($this->_sticky == true) {
            return $this->Connection->getMasterLink();
        }

        if(!is_null($name)){
            return $this->Connection->getLink($name);
        }

        return $this->Connection->getLink($this->linkName);
    }

    /**
     * @param array $data
     * @param bool $is_replace
     * @return int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function insert(Array $data = [], $is_replace = false)
    {
        $Link = $this->getLink(true);
        //set data
        $this->set($data);
        //get the insert sql
        $SQL = $this->toInsertSql($Link->getGrammar(), $is_replace);

        //execute the sql
        $this->exec($SQL);
        //get the inserted Id
        $lastInsertId = $Link->getPdo()->lastInsertId();

        return intval($lastInsertId);
    }

    /**
     * 批量插入
     * @param array $data
     * @param int $size
     * @param bool $is_replace
     * @return bool
     * @throws \Exception
     * itwri 2019/12/30 10:27
     */
    public function insertAll(Array $data,$size = 1000, $is_replace = false){

        /**
         * 处理数据
         */
        $insertData = [];
        $tempData = [];
        foreach ($data as $key => $datum) {
            if (is_string($key) && (is_string($datum) || is_numeric($datum) || $datum instanceof Expression)) {
                $insertData[$key] = $datum;
            }

            if(is_array($datum)){
                $tempKey = implode('-',array_keys($datum));
                if(!isset($tempData[$tempKey])){
                    $tempData[$tempKey] = [];
                }
                $tempData[$tempKey] = $datum;
            }
        }

        $k = implode('-',array_keys($insertData));
        if(isset($tempData[$k])){
            $tempData[$k][] = $insertData;
        }

        /**
         * 开启事务
         */
        $this->startTrans();
        try{

            $Link = $this->getLink(true);

            foreach ($tempData as $tempDatum) {
                $count = count($tempDatum);
                $newData = [];
                $successCount = 0;
                foreach ($tempDatum as $key => $datum) {
                    $newData[] = $datum;

                    if(count($newData) >= $size){

                        //set data
                        $this->set($newData);
                        //get the insert sql
                        $SQL = $this->toInsertSql($Link->getGrammar(), $is_replace);

                        //execute the sql
                        $num = $this->exec($SQL);
                        $successCount += (int)$num;

                        //已执行则重置
                        $newData = [];

                    }
                }

                //set data
                $this->set($newData);
                //get the insert sql
                $SQL = $this->toInsertSql($Link->getGrammar(), $is_replace);

                //execute the sql
                $num = $this->exec($SQL);
                $successCount += (int)$num;

                //
                if($successCount != $count){
                    throw new \Exception('Error with the data.');
                }
            }

            $this->commit();

            return true;
        }catch (\Exception $exception){

            $this->errorArr[] = (string)$exception;

            /**
             * 回滚事务
             */
            $this->rollback();

            if($this->debug == true){
                die((string)$exception);
            }
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool|int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function update(array $data = [])
    {
        if(!empty($data)){
            //set data
            $this->set($data);
        }

        //get the update sql;
        $SQL = $this->toUpdateSql($this->getLink(true)->getGrammar());

        return $this->exec($SQL);
    }

    /**
     * @return bool|int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function delete()
    {
        //get the delete sql
        $SQL = $this->toDeleteSql($this->getLink(true)->getGrammar());

        return $this->exec($SQL);
    }

    /**
     * @return int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function count()
    {
        $this->limit(1);
        //get the select sql
        $SQL = $this->toCountSql($this->getLink()->getGrammar());

        //query
        $st = $this->query($SQL);

        if ($st !== false) {
            return intval($st->fetch(\PDO::FETCH_ASSOC)['__COUNT__']);
        }
        return 0;
    }

    /**
     * @param string $fields
     * @param int $fetch_type
     * @return bool|mixed
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function get($fields = '*', $fetch_type = \PDO::FETCH_ASSOC)
    {
        parent::fields($fields);

        $this->limit(1);
        //get the select sql
        $SQL = $this->toSelectSql($this->getLink()->getGrammar());
        //query
        $st = $this->query($SQL);
        if ($st !== false) {
            //return the result
            return $st->fetch($fetch_type);
        }
        return false;
    }

    /**
     * @param string $fields
     * @param int $fetch_type
     * @return bool|mixed
     * itwri 2019/12/19 14:29
     */
    public function first($fields = '*', $fetch_type = \PDO::FETCH_ASSOC){
        return call_user_func_array([$this,'get'],[$fields,$fetch_type]);
    }

    /**
     * @param null $fields
     * @param int $fetch_type
     * @return array
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function getAll($fields = null, $fetch_type = \PDO::FETCH_ASSOC)
    {
        parent::fields($fields);

        //get the select sql
        $SQL = $this->toSelectSql($this->getLink()->getGrammar());

        //query
        $st = $this->query($SQL);
        if ($st) {
            return $st->fetchAll($fetch_type);
        }
        return array();
    }

    /**
     * @param string $fields
     * @param int $fetch_type
     * @return mixed
     */
    function select($fields = '*', $fetch_type = \PDO::FETCH_ASSOC)
    {
        return call_user_func_array([$this, 'getAll'], func_get_args());
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    function paginator($page=1,$pageSize=10){

        $page = $page < 1 ? 1 : $page;
        $offset = ($page-1)*$pageSize;

        if(func_num_args()<2){
            $limit = $this->Limit->data();
            isset($limit[1]) && $pageSize = $limit[1]>0?$limit[1]:$pageSize;
        }

        $this->limit($offset,$pageSize);

        $SQL = $this->toCountSql($this->getLink()->getGrammar());

        $total = 0;
        $st = $this->query($SQL);
        if ($st !== false) {
            $total = intval($st->fetch(\PDO::FETCH_ASSOC)['__COUNT__']);
        }

        $totalPage = ceil($total/$pageSize);

        $this->roll('select,from,join,where,group,having,order,limit');
        $list = $this->select();


        return ['total'=>$total,'items'=>$list,'pages_count'=>$totalPage,'page'=>$page];
    }

    /**
     * @param $statement
     * @return bool|\PDOStatement
     */
    public function query($statement)
    {
        $res = false;
        $this->trace(function () use ($statement, &$res) {

            $time_arr = explode(' ', microtime(false));
            $start_time = $time_arr[0] + $time_arr[1];

            /**
             * =====================================================
             */
            $error_info = [];
            if ($this->isWriteAction($statement)) {
                $res = $this->getLink(true)->getPdo()->query($statement);
                $res === false && $error_info = $this->getLink(true)->getPdo()->errorInfo();
            } else {
                $res = $this->getLink()->getPdo()->query($statement);
                $res === false && $error_info = $this->getLink()->getPdo()->errorInfo();
            }
            /**
             * =====================================================
             */

            $time_arr = explode(' ', microtime(false));
            $end_time = $time_arr[0] + $time_arr[1];

            $runtime = number_format($end_time - $start_time, 10);

            $log_info = sprintf("SQL Query: %s %s\r\n", $statement, ($res != false ? '[true' : '[false').",Runtime:{$runtime}]");

            $this->log($log_info);

            //重置
            $this->linkName = null;

            $this->cacheSql($statement);

            //
            !empty($error_info) && $this->errorArr[] = $error_info;

            if ($this->debug) {
                print_r($log_info);
                !empty($error_info) && print_r(sprintf("SQL Error: %s\r\n", var_export($error_info, true)));
            }
        });
        return $res;
    }

    /**
     * @param $statement
     * @return bool|int
     */
    public function exec($statement)
    {

        $res = false;
        $this->trace(function () use ($statement, &$res) {

            $time_arr = explode(' ', microtime(false));
            $start_time = $time_arr[0] + $time_arr[1];

            $error_info = [];
            if ($this->isWriteAction($statement)) {
                $res = $this->getLink(true)->getPdo()->exec($statement);
                $res === false && $error_info = $this->getLink(true)->getPdo()->errorInfo();
            } else {
                $res = $this->getLink()->getPdo()->exec($statement);
                $res === false && $error_info = $this->getLink()->getPdo()->errorInfo();
            }

            $time_arr = explode(' ', microtime(false));
            $end_time = $time_arr[0] + $time_arr[1];

            $runtime = number_format($end_time - $start_time, 10);

            $log_info = sprintf("SQL Execute: %s %s\r\n", $statement, ($res != false ? '[true' : '[false').",Runtime:{$runtime}]");

            $this->log($log_info);

            $this->linkName = null;

            $this->cacheSql($statement);

            //
            !empty($error_info) && $this->errorArr[] = $error_info;
            if ($this->debug) {
                print_r($log_info);
                !empty($error_info) && print_r(sprintf("SQL Error: %s\r\n", var_export($error_info, true)));
            }
        });
        return $res;
    }

    /**
     * @param $closure
     * @return $this
     * itwri 2019/12/2 16:33
     */
    public function masterHandle($closure)
    {
        $this->_sticky = true;
        if (is_callable($closure)) {
            call_user_func_array($closure, [$this]);
        }
        $this->_sticky = false;
        return $this;
    }

    /**
     * @param $statement
     * @return bool
     */
    protected function isWriteAction($statement)
    {
        $statement = strtolower(trim($statement));
        if (strpos($statement, 'insert') !== false
            || strpos($statement, 'delete') !== false
            || strpos($statement, 'update ') !== false
            || strpos($statement, 'replace') !== false
            || strpos($statement, 'truncate') !== false
            || strpos($statement, 'create') !== false
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    function startTrans(){
        if(!$this->getLink()->getPdo()->inTransaction()){
            return $this->getLink()->getPdo()->beginTransaction();
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    function commit(){
        if($this->getLink()->getPdo()->inTransaction()){
            return $this->getLink()->getPdo()->commit();
        }
        return false;
    }


    /**
     * @return bool
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function rollback()
    {
        if($this->getLink()->getPdo()->inTransaction()){
            return $this->getLink()->getPdo()->rollBack();
        }
        return false;
    }

    /**
     * @param \Closure $closure
     * @return bool|string
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function transaction(\Closure $closure){
        $this->startTrans();
        try{
            $this->_sticky = true;
            $res = call_user_func_array($closure,[$this]);
            if($res === false){
                throw new ErrorException('User Abort.');
            }
            $this->commit();
            $this->_sticky = false;
            return true;
        }catch (\Exception $exception){
            $this->_sticky = false;
            $this->rollback();

            $this->log((string)$exception);

            $this->errorArr[] = $exception->getMessage();
            return false;
        }
    }


    /**
     * @param $callback
     * @return $this
     */
    public function trace($callback)
    {
        if ($callback instanceof \Closure) {
            /**
             * do something
             */
            call_user_func_array($callback, array());
        }
        return $this;
    }

    /**
     * @var array
     */
    protected $logSQLs = array();

    /**
     * pop out the last one SQL;
     * @return string
     */
    public function getLastSql()
    {
        $SQL = $this->logSQLs[count($this->logSQLs) - 1];
        return $SQL ? $SQL : "";
    }

    /**
     * @param string $sql
     * @return $this
     */
    protected function cacheSql($sql)
    {
        $this->logSQLs[] = $sql;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getErrorInfo(){
        if(count($this->errorArr)>0){
            return $this->errorArr[count($this->errorArr) - 1];
        }
        return null;
    }

    /**
     * @param $field
     * @param int $inc
     * @return bool|int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function setInc($field, $inc = 1)
    {
        return $this->set($field,new Expression($field."+".$inc))->update();
    }

    /**
     * @param $field
     * @param int $inc
     * @return bool|int
     * @throws \Exception
     * itwri 2019/12/19 13:59
     */
    public function setDec($field, $inc = 1)
    {
        return $this->set($field,new Expression($field."-".$inc))->update();
    }

    /**
     * @param $content
     * @param string $error_level
     * itwri 2020/2/26 0:32
     */
    public function log($content,$error_level = 'info'){
        try{

            if(!is_writable($this->logConfig['directory'])){
                throw new \ErrorException('the log directory cannot be written.');
            }
            $path = $this->logConfig['directory'].'/logs/'.date('Ymd');
            if(!is_dir($path)){
                @mkdir($path,755,true);
            }

            $log_file = $path."/".date('d').".log";

            if(is_file($log_file)){
                $file_size = filesize($log_file);
                $file_time = filectime($log_file);
                if($file_size > 1024 * 1024 *2){
                    @rename($log_file,$path."/".date('d')."-".$file_time.".log");
                }
            }

            $content = ((is_string($content)||is_numeric($content))?$content:var_export($content, true));

            //end time
            $time_arr = explode(' ', microtime(false));

            @file_put_contents($log_file, '['.date('Y-m-d H:i:s').$time_arr[0]."] [{$error_level}] {$content}", FILE_APPEND);


        }catch (\ErrorException $exception){
            die((string)$exception);
        }
    }
}