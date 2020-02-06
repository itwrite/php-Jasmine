<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/1/22
 * Time: 14:34
 */

namespace Jasmine\util;


class Tree
{
    /**
     * @param array $list
     * @param int $parent_id
     * @param string $parent_key
     * @return array
     * itwri 2020/1/22 14:40
     */
    public static function parse(array $list, $parent_id = 0, $parent_key = 'parent_id'){
        $result = [];
        if(is_array($list)){
            $res1 = self::findChildren($list,$parent_id,$parent_key);

            if(!empty($res1['children'])){
                foreach ($res1['children'] as $child) {

                    $children = self::parse($res1['remain'], $child['id'],$parent_key);
                    if(!empty($children)){
                        $child['children'] = $children;
                    }

                    $result[] = $child;
                }
            }

        }
        return $result;
    }

    /**
     * @param $data
     * @param $parent_id
     * @param string $parent_key
     * @return array
     * itwri 2020/1/22 14:40
     */
    public static function findChildren($data,$parent_id, $parent_key = 'parent_id'){
        $result = ['children'=>[],'remain'=>[]];
        if(is_array($data)){
            foreach ($data as $datum) {
                if(isset($datum[$parent_key]) && $datum[$parent_key] == $parent_id){
                    $result['children'][] = $datum;
                }else{
                    $result['remain'][] = $datum;
                }
            }
        }
        return $result;
    }
}