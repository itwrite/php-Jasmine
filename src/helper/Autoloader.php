<?php

/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/29
 * Time: 14:09
 */

namespace Jasmine\helper;


/**
 * Implements a lightweight PSR-0 compliant autoloader for Predis.
 *
 * @author Eric Naeseth <eric@thumbtack.com>
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class Autoloader
{
    protected static $prefixArr = [];

    public static function extend(array $prefixArr){
        foreach ($prefixArr as $prefix=>$baseDirectory) {
            self::$prefixArr[$prefix] = $baseDirectory;
        }
    }
    /**
     * Registers the autoloader class with the PHP SPL autoloader.
     * @param array $prefixArr
     * @param bool $prepend Prepend the autoloader on the stack instead of appending it.
     */
    public static function register(array $prefixArr,$prepend = false)
    {
        self::extend($prefixArr);
        spl_autoload_register([new static(),'autoload'], true, $prepend);
    }

    /**
     * Loads a class from a file using its fully qualified name.
     *
     * @param string $className Fully qualified name of a class.
     */
    public function autoload($className)
    {
        $className = $className{0} == '\\' ? substr($className, 1) : $className;

        foreach (self::$prefixArr as $prefix=>$baseDirectory) {

            if (0 === strpos($className, $prefix)) {
                $parts = explode('\\', substr($className, strlen($prefix)));
                $filePath = $baseDirectory.'/'.implode('/', $parts).'.php';

                if (is_file($filePath)) {
                    require $filePath;
                    break;
                }
            }
        }

    }
}