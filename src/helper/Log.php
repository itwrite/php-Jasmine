<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/31
 * Time: 1:11
 */

namespace Jasmine\helper;


use Jasmine\library\file\File;
use Jasmine\library\log\interfaces\LoggerAwareInterface;
use Jasmine\library\log\interfaces\LoggerInterface;
use Jasmine\library\log\Logger;
use Jasmine\library\log\LogLevel;

class Log implements LoggerInterface,LoggerAwareInterface
{
    protected static $instance;
    protected $Logger = null;

    function __construct()
    {
       $this->setLogger(new Logger('debug'));
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger){
        $this->Logger = $logger;
    }

    /**
     * @return Log
     * itwri 2020/3/8 14:06
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new static();
        }
        return self::$instance;
    }
    /**
     * @return Logger|null
     * itwri 2020/2/26 23:10
     */
    protected function getLogger(){
        return $this->Logger;
    }


    /**
     * @param $level
     * @param $message
     * @param array $context
     * itwri 2020/2/26 23:12
     */
    public function write($level,$message, array $context = array()){
        try{
            $runtime_path = Config::get('PATH_RUNTIME','');
            if(!is_dir($runtime_path)){
                throw new \ErrorException('./runtime is not a directory.');
            }
            if(!File::init()->isWritable($runtime_path)){
                throw new \ErrorException('./runtime cannot be written.');
            }
            $path = $runtime_path.'/logs/'.date('Ym');
            if(!is_dir($path)){
                @mkdir($path,755,true);
            }

            $log_file = $path."/".date('d').".log";

            if(is_file($log_file)){
                $file_size = filesize($log_file);
                $file_time = filectime($log_file);
                if($file_size > 1024 * 1024 *2){
                    @rename($log_file,$path."/".date('d')."-".$file_time.".log");
                }
            }

            $content = ((is_string($message)||is_numeric($message))?$message:var_export($message, true));

            self::getLogger()->setOutput($log_file)->write($level,$content,$context);

        }catch (\ErrorException $exception){
            die((string)$exception);
        }
    }

    /**
     * System is unusable.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function emergency($message, array $context = array())
    {
        self::write(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function alert($message, array $context = array())
    {
        self::write(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function critical($message, array $context = array())
    {
        self::write(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function error($message, array $context = array())
    {
        self::write(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function warning($message, array $context = array())
    {
        self::write(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function notice($message, array $context = array())
    {
        self::write(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function info($message, array $context = array())
    {
        self::write(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param mixed $message
     * @param array  $context
     *
     * @return void
     */
     public function debug($message, array $context = array())
    {
        self::write(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * itwri 2020/3/8 14:45
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getInstance(),$name],$arguments);
    }
}