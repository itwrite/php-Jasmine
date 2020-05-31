<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/5/11
 * Time: 10:25
 */

namespace Jasmine\library\validate\interfaces;


interface ValidatorInterface
{

    const SCENE_CREATE = 'create';
    const SCENE_UPDATE = 'update';
    const SCENE_DELETE = 'delete';
    const SCENE_LIST = 'list';
    /**
     * @param array $data
     * @return mixed
     * itwri 2020/5/11 10:26
     */
    public function with(array $data);

    /**
     * @param mixed $scene
     * @return mixed
     * itwri 2020/5/11 10:26
     */
    public function check($scene);

    /**
     * @return mixed
     * itwri 2020/5/11 10:28
     */
    public function getError();

    /**
     * @param string $scene
     * @param array $rules
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function setRules(string $scene,array $rules);

    /**
     * @param string $scene
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function getRules(string $scene);

    /**
     * @param $scene
     * @param $field
     * @param $rule
     * @param null $ruleFun
     * @param null $msg
     * @return mixed
     * itwri 2020/5/11 12:40
     */
    public function addRule($scene,$field, $rule, $ruleFun = null, $msg = null);
}