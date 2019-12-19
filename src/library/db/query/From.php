<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/4/24
 * Time: 12:10
 */

namespace Jasmine\library\db\query;

use Jasmine\library\db\query\capsule\Expression;
use Jasmine\library\db\query\schema\Eloquent;

require_once 'schema/Eloquent.php';

class From extends Eloquent
{
    /**
     * Desc:
     * User: Peter
     * Date: 2019/3/6
     * Time: 19:40
     *
     * @param $table
     * @param bool $append
     * @return $this
     */
    public function table($table, $append = false)
    {
        if ($append) {
            if (is_array($table)) {
                foreach ($table as $val) {
                    $this->table($val);
                }
            } elseif (is_string($table) || $table instanceof Expression) {
                $this->data[] = $table;
            } elseif ($table instanceof \Closure) {
                $sql = call_user_func($table);
                $this->data[] = isset($sql) ? (($sql instanceof Expression) ? $sql : (string)$sql) : '';
            }
        } else {
            $this->data = [];
            $this->table($table, true);
        }

        return $this;
    }
}