<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2018/1/2
 * Time: 21:30
 */

namespace Jasmine\library\view\compiler\traits;


trait Properties {

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

}