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
    public static function parse(array $list, $parent_id = 0, $parent_key = 'parent_id')
    {
        $result = [];
        if (is_array($list)) {
            $res1 = self::findChildren($list, $parent_id, $parent_key);

            if (!empty($res1['children'])) {
                foreach ($res1['children'] as $child) {

                    $children = self::parse($res1['remain'], $child['id'], $parent_key);
                    if (!empty($children)) {
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
    public static function findChildren($data, $parent_id, $parent_key = 'parent_id')
    {
        $result = ['children' => [], 'remain' => []];
        if (is_array($data)) {
            foreach ($data as $datum) {
                if (isset($datum[$parent_key]) && $datum[$parent_key] == $parent_id) {
                    $result['children'][] = $datum;
                } else {
                    $result['remain'][] = $datum;
                }
            }
        }
        return $result;
    }

    /**
     * @param $item
     * @param $data
     * @return array
     * itwri 2019/12/17 18:14
     */
    public static function getPaths($item, $data)
    {
        $result = [$item];
        $parent = self::findItem($item['parent_id'], $data);
        if ($parent) {
            $list = self::getPaths($parent, $data);
            foreach ($list as $value) {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * @param $id
     * @param $data
     * @param string $field
     * @return mixed|null
     * itwri 2020/1/7 17:19
     */
    public static function findItem($id, $data, $field = 'id')
    {
        if (is_array($data)) {
            foreach ($data as $datum) {
                if (isset($datum[$field]) && $datum[$field] == $id) {
                    return $datum;
                }
            }
        }
        return null;
    }

    /**
     * @param $list
     * @param array $result
     * @param int $parent_id
     * @param array $_paths
     * itwri 2020/3/10 11:37
     */
    public static function  adverse($list, &$result = [], $parent_id = 0, $_paths = [])
    {
        /**
         * 找到子项
         */
        $res = Arr::findChildren($list, $parent_id);
        if (!empty($res['children'])) {
            //如果存在子项
            foreach ($res['children'] as $child) {
                //复制使用
                $paths = $_paths;
                //追加名称
                array_push($paths, $child);

                /**
                 * 继续查找下一级
                 */
                $res1 = Arr::findChildren($res['remain'], $child['id']);
                if (empty($res1['children'])) {
                    $child['paths'] = $paths;
                    $result[] = $child;
                } else {
                    self::adverse($res['remain'], $result, $child['id'], $paths);
                }
            }
        }
    }

}