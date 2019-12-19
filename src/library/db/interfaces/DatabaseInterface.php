<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 13:39
 */

namespace Jasmine\library\db\interfaces;


interface DatabaseInterface
{
    /** insert into table
     * @param array $data
     * @param bool $is_replace
     * @return mixed
     * itwri 2019/12/19 14:33
     */
    public function insert(Array $data = [], $is_replace = false);

    /**
     * update data for table
     * @param array $data
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function update(array $data = []);

    /**
     * @param $field
     * @param int $inc
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function setInc($field, $inc = 1);

    /**
     * @param $field
     * @param int $inc
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function setDec($field, $inc = 1);

    /**
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function delete();

    /**
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function count();

    /**
     * get the first row of result
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function get();

    /**
     * @return mixed
     * itwri 2019/12/19 14:34
     */
    public function first();

    /**
     * get all rows
     * @return mixed
     * itwri 2019/12/19 14:35
     */
    public function getAll();

    /**
     * SQL
     * @param $statement
     * @return mixed
     * itwri 2019/12/19 14:35
     */
    public function query($statement);

    /**
     * @param $statement
     * @return mixed
     * itwri 2019/12/19 14:35
     */
    public function exec($statement);

    /**
     * @return mixed
     * itwri 2019/12/19 14:35
     */
    public function rollback();

    /**
     * @return mixed
     * itwri 2019/12/19 14:36
     */
    public function commit();
}