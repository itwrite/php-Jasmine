<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/31
 * Time: 1:11
 */

namespace Jasmine\helper;


use Jasmine\library\file\File;

class Log
{
    static function write($content,$error_level = 'info'){
        try{
            $runtime_path = Config::get('PATH_RUNTIME','');
            if(!is_dir($runtime_path)){
                throw new \ErrorException('./runtime is not a directory.');
            }
            if(!File::init()->isWritable($runtime_path)){
                throw new \ErrorException('./runtime cannot be written.');
            }
            $path = $runtime_path.'/logs/'.date('Ymd');
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

            $content = ((is_string($content)||is_numeric($content))?$content:var_export($content, true));
            $line = str_pad('-',50,'-')."\r\n".str_pad('-',50,'-')."\r\n";

            @file_put_contents($log_file, '['.date('Y-m-d H:i:s')."][{$error_level}]\r\n", FILE_APPEND);
            @file_put_contents($log_file, $content . "\r\n".$line, FILE_APPEND);
        }catch (\ErrorException $exception){
            die($exception->getMessage());
        }
    }
}