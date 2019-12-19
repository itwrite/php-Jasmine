<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 14:07
 */

namespace Jasmine\library\db\connection\interfaces;


use Jasmine\library\db\connection\capsule\Link;

interface ConnectionInterface
{

    /**
     * @param string $flag
     * @return mixed
     * itwri 2019/12/19 13:10
     */
    public function getConfig($flag = 'write');

    /**
     * @param bool $master
     * @return Link
     * itwri 2019/12/19 13:07
     */
    public function getLink($master = true);

    /**
     * @return mixed
     * itwri 2019/12/19 13:16
     */
    public function getMasterLink();

    /**
     * @return mixed
     * itwri 2019/12/19 13:16
     */
    public function getReadLink();
}