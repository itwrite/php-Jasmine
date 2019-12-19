<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 16:57
 */

namespace Jasmine\library\db\grammar;

require_once 'Grammar.php';
class Mysql extends Grammar
{
    /**
     * @var array
     */
    protected $operators = array(
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'in', 'not in',
        'like', 'not like', 'between',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
    );
}