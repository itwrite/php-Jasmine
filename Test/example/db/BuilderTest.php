<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/6
 * Time: 10:50
 */

namespace Test\example\db;

use Jasmine\library\db\Builder;
use Jasmine\library\db\grammar\Mysql;

require_once __DIR__ . '/../../../src/library/db/Builder.php';
require_once __DIR__ . '/../../../src/library/db/grammar/Mysql.php';

class BuilderTest
{
    protected $builder = null;
    protected $grammar = null;

    function __construct()
    {
        $this->builder = new Builder();
        $this->grammar = new Mysql();
    }

    function test1()
    {
        $this->builder
            ->fields('u.*')
            ->table('user u')
            ->join('role r', 'r.id=u.role_id', 'left')
            ->where('u.id', '=', 1)
            ->having('count(u.id)>0')
            ->whereIn('u.id',[44,123])
            ->limit(12)
            ->orderBy('u.id desc,r.id desc');

        echo $this->builder->toSelectSql($this->grammar);
    }
}


$test = new BuilderTest();

$test->test1();
