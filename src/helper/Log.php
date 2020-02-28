<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/31
 * Time: 1:11
 */

namespace Jasmine\helper;


use Jasmine\library\file\File;
use Jasmine\library\log\Logger;
use Jasmine\library\log\LogLevel;

class Log
{
    static protected $Logger = null;

    /**
     * @return Logger|null
     * itwri 2020/2/26 23:10
     */
    static protected function getLogger(){
        if(self::$Logger == null){
            self::$Logger = new Logger('debug');
        }
        return self::$Logger;
    }


    /**
     * @param $level
     * @param $message
     * @param array $context
     * itwri 2020/2/26 23:12
     */
    static protected function write($level,$message, array $context = array()){
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

            self::getLogger()->setOutput($log_file)->log($level,$content,$context);

        }catch (\ErrorException $exception){
            die((string)$exception);
        }
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function emergency($message, array $context = array())
    {
        self::write(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function alert($message, array $context = array())
    {
        self::write(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function critical($message, array $context = array())
    {
        self::write(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function error($message, array $context = array())
    {
        self::write(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function warning($message, array $context = array())
    {
        self::write(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function notice($message, array $context = array())
    {
        self::write(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function info($message, array $context = array())
    {
        self::write(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function debug($message, array $context = array())
    {
        self::write(LogLevel::DEBUG, $message, $context);
    }
}