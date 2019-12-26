<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/17
 * Time: 3:14
 */

namespace Test\example\db;
use Jasmine\library\db\Database;
use Jasmine\library\db\grammar\Mysql;

require_once __DIR__ . '/../../../framework/src/library/db/Database.php';
require_once __DIR__ . '/../../../framework/src/library/db/grammar/Mysql.php';

class DatabaseTest
{
    protected $db = null;
    protected $grammar = null;

    function __construct()
    {
        $db_config = require_once __DIR__.'/../../config/db.php';
        $this->db = new Database($db_config['connections']['mysql']);
        $this->grammar = new Mysql();
    }

    function testLink()
    {
        $res = $this->db->link('a')->table('ht_vip_withdrawal')->select();

        var_dump($res);
    }

    /**
     * @throws \Exception
     * itwri 2019/12/19 14:07
     */
    function testMultipleDbConnection(){

        $this->db->masterHandle(function (Database $database){
            $database->debug(1)->table('user')->select();
            $database->table('ht_vip_withdrawal')->get();
        });

        $res = $this->db->link(false)->table('ht_vip_withdrawal')->get();
        var_dump($res);
    }
}


$test = new DatabaseTest();

try{
    $test->testMultipleDbConnection();
}catch (\Exception $exception){
    echo $exception->getMessage();
}

