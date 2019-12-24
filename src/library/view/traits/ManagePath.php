<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/21
 * Time: 0:47
 */

namespace Jasmine\library\view\traits;


trait ManagePath
{
    protected $viewDirectory = '';
    protected $cacheDirectory = '';
    protected $name = '';
    protected $ext = '.blade.php';

    /**
     * @param $path
     * @return $this
     */
    public function setViewDirectory($path)
    {
        $this->viewDirectory = $path;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setCacheDirectory($path)
    {
        $this->cacheDirectory = $path;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTemplateFilename()
    {
        return $this->viewDirectory . DIRECTORY_SEPARATOR . str_replace(['.', '/', '\\'], DIRECTORY_SEPARATOR, $this->name) . $this->ext;
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @return string
     */
    protected function getCompiledFilename()
    {
        return $this->cacheDirectory . DIRECTORY_SEPARATOR . sha1($this->getTemplateFilename()) . '.php';
    }

}