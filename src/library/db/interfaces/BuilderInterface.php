<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 12:47
 */

namespace Jasmine\library\db\interfaces;

interface BuilderInterface
{
    public function fields($fields = '*');

    public function table($table, $append = false);

    public function join($table, $on = '', $type = '');

    public function where($field, $operator = '', $value = '', $boolean = 'and');

    public function whereIn($field, Array $values, $boolean = 'and');

    public function whereNotIn($field, Array $values, $boolean = 'and');

    public function whereBetween($field, Array $values, $boolean = 'and');

    public function whereLike($field, $value, $boolean = 'and');

    public function orderBy($field = '');

    public function groupBy($field = '');

    public function having($field, $operator = '', $value = '', $boolean = 'and');

    public function limit($offset = 0, $page_size = 10);

    public function set($field, $value = '');

    public function clear($operation = '');

    public function roll($options = '');
}