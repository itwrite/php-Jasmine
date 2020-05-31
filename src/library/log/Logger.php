<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/26
 * Time: 22:48
 */

namespace Jasmine\library\log;



use Jasmine\library\log\abstracts\AbstractLogger;
use Jasmine\library\log\exception\InvalidArgumentException;

/**
 * Minimalist PSR-3 logger designed to write in stderr or any other stream.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Logger extends AbstractLogger
{
    private static $levels = array(
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    );

    private $minLevelIndex;
    private $formatter;
    private $handle;

    public function __construct(string $minLevel = null, $output = 'php://stderr', callable $formatter = null)
    {
        if (null === $minLevel) {
            $minLevel = LogLevel::WARNING;

            if (isset($_ENV['SHELL_VERBOSITY']) || isset($_SERVER['SHELL_VERBOSITY'])) {
                switch ((int) (isset($_ENV['SHELL_VERBOSITY']) ? $_ENV['SHELL_VERBOSITY'] : $_SERVER['SHELL_VERBOSITY'])) {
                    case -1: $minLevel = LogLevel::ERROR; break;
                    case 1: $minLevel = LogLevel::NOTICE; break;
                    case 2: $minLevel = LogLevel::INFO; break;
                    case 3: $minLevel = LogLevel::DEBUG; break;
                }
            }
        }

        if (!isset(self::$levels[$minLevel])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $minLevel));
        }

        $this->minLevelIndex = self::$levels[$minLevel];
        $this->formatter = $formatter ?: array($this, 'format');
        $this->setOutput($output);
    }

    /**
     * @param $output
     * @return $this
     * itwri 2020/2/26 23:03
     */
    public function setOutput($output){
        if (false === $this->handle = is_resource($output) ? $output : @fopen($output, 'a')) {
            throw new InvalidArgumentException(sprintf('Unable to open "%s".', $output));
        }
        return $this;
    }


    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return string
     * itwri 2020/2/26 22:50
     */
    private function format($level, string $message, array $context)
    {
        //time
        $timeArr = explode(' ', microtime(false));

        if (false !== strpos($message, '{')) {
            $replacements = array();
            foreach ($context as $key => $val) {
                if (null === $val || is_scalar($val) || (\is_object($val) && method_exists($val, '__toString'))) {
                    $replacements["{{$key}}"] = $val;
                } elseif ($val instanceof \DateTimeInterface) {
                    $replacements["{{$key}}"] = $val->format('Y-m-d H:i:s').substr(strval($timeArr[0]),1);
                } elseif (\is_object($val)) {
                    $replacements["{{$key}}"] = '[object '.\get_class($val).']';
                } else {
                    $replacements["{{$key}}"] = '['.\gettype($val).']';
                }
            }

            $message = strtr($message, $replacements);
        }

        return sprintf('%s [%s] %s', date('Y-m-d H:i:s').substr(strval($timeArr[0]),1), $level, $message).\PHP_EOL;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function write($level, $message, array $context = array())
    {
        if (!isset(self::$levels[$level])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
        }

        if (self::$levels[$level] < $this->minLevelIndex) {
            return;
        }

        $formatter = $this->formatter;
        fwrite($this->handle, $formatter($level, $message, $context));
    }
}