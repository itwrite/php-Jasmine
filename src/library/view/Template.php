<?php /** @noinspection ALL */

/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/4/2
 * Time: 21:34
 */

namespace Jasmine\library\view;

use Jasmine\library\view\compiler\Compiler;
use Jasmine\library\view\compiler\traits\CompileStatements;
use Jasmine\library\view\interfaces\TemplateInterface;
use Jasmine\library\view\traits\ManageData;
use Jasmine\library\view\traits\ManagePath;

require_once 'traits/ManageData.php';
require_once 'traits/ManagePath.php';
require_once 'compiler/Compiler.php';
require_once 'interfaces/TemplateInterface.php';

class Template implements TemplateInterface
{
    use CompileStatements, ManageData, ManagePath;

    /**
     * @var Compiler|null
     */
    protected $Compiler = null;

    function __construct($viewDorectory, $cacheDirectory = '', $dataPublic = true)
    {
        $this->setViewDirectory($viewDorectory);
        $this->setCacheDirectory($cacheDirectory);
        $this->setDataPublic($dataPublic == true);

        $this->Compiler = new Compiler();

        $this->getCompiler()->extend([$this, 'compile']);
    }


    /**
     * @param $view
     * @param array $data
     * @return $this
     */
    public function make($view, array $data = [])
    {
        $this->name = $view;
        $this->assign($data);
        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function compile($value)
    {
        return $this->compileStatements($value);
    }

    /**
     * @return Compiler|null
     */
    protected function getCompiler()
    {
        return $this->Compiler;
    }


    /**
     * @return bool
     */
    protected function isExpired()
    {
        $templateFilename = $this->getTemplateFilename();
        $compiledFilename = $this->getCompiledFilename();

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!file_exists($compiledFilename)) {
            return true;
        }

        return filemtime($templateFilename) >= filemtime($compiledFilename);
    }

    /**
     * @return $this
     */
    protected function doCompile()
    {
        $templateFilename = $this->getTemplateFilename();
        $compiledFilename = $this->getCompiledFilename();

        if(!is_file($templateFilename)){
            die("file[{$templateFilename}] is not exists.");
        }

        $cacheDir = dirname($compiledFilename);

        if (!is_null($this->cacheDirectory)) {
            /**
             * init directory
             */
            !is_dir($cacheDir) && mkdir($cacheDir, 755, true);
            //
            $contents = "<?php /* {$templateFilename} */ ?>\n" . $this->getCompiler()->compileString(file_get_contents($templateFilename));

            $this->cacheCompiled($contents);
        }
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     * itwri 2019/12/19 17:22
     */
    public function render()
    {
        if ($this->isExpired()) {
            try{
                $this->doCompile();
            }catch (\Exception $exception){
                echo $exception->getMessage();
            }
        }

        $compiledFilename = $this->getCompiledFilename();
        return file_exists($compiledFilename) ? $this->evaluate($compiledFilename) : "";
    }

    /**
     * @param string $__path
     * @return string
     * @throws \Exception
     * itwri 2019/12/19 17:21
     */
    protected function evaluate($__path)
    {
        $obLevel = ob_get_level();

        ob_start();

        /**
         * 是否是全局变量
         */
        if ($this->dataPublic) {
            foreach (self::$data as $key => $value)
                $$key = $value;
        } else {
            foreach ($this->_data as $key => $value)
                $$key = $value;
        }

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            include $__path;
        } catch (\Exception $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception $e
     * @param  int $obLevel
     * @return void
     *
     * @throws \Exception
     */
    protected function handleViewException(\Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }

    /**
     * @param string $compiledContent
     * @return bool|int
     */
    protected function cacheCompiled($compiledContent = '')
    {
        $cachePath = $this->getCompiledFilename();
        return file_put_contents($cachePath, $compiledContent);
    }

    /**
     * ====================================================================================
     * ====================================================================================
     * ====================================================================================
     */
    /**
     * @param $expression
     * @return string
     */
    protected function compileInclude($expression)
    {
        $expression = $this->Compiler->stripParentheses($expression);
        $class = self::class;
        return "<?php echo (new {$class}('{$this->viewDirectory}','{$this->cacheDirectory}',true))->make({$expression})->render();?>";
    }

    /**
     * @param $expression
     * @return string
     */
//    protected function compileSection($expression){
//        $expression = $this->Compiler->stripParentheses($expression);
//        $class = self::class;
//        $arr = explode('\view\\',$class);
//
    /*        return "<?php ".$arr[0]."\View::make('{$this->viewName}')->startSection({$expression});?>";*/
//    }

    /**
     * @return string
     */
//    protected function compileEndSection(){
    /*        return  "<?php isset(\$___env)&&\$___env->endSection();?>";*/
//    }

}